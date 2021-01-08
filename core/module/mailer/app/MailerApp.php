<?php

namespace uve\core\mdoule\mailer\app;

use Throwable;
use uve\core\app\App;
use uve\core\Logger;
use uve\core\module\mailer\app\api\ApiResponse;
use uve\core\module\mailer\app\api\ApiClientAbstract;
use uve\core\module\mailer\app\api\RequestBuilderAbstract;
use uve\core\module\mailer\datalayer\MailerDataLayer;
use uve\core\module\mailer\model\Email;
use uve\core\module\mailer\model\MailerItem;
use uve\core\module\mailer\model\MailerLogItem;
use uve\core\util\DateUtil;

class MailerApp extends App
{
    private MailerDataLayer $dataLayer;
    private ApiClientAbstract $apiClient;
    private RequestBuilderAbstract $requestBuilder;

    public function __construct(
        Logger $logger,
        MailerDataLayer $dataLayer,
        ApiClientAbstract $apiClient,
        RequestBuilderAbstract $requestBuilder
    ) {
        parent::__construct($logger, 'Mailer App');
        $this->dataLayer = $dataLayer;
        $this->apiClient = $apiClient;
        $this->requestBuilder = $requestBuilder;
    }

    public function run() {
        $this->execute(function () {
            $this->logger->logInfo($this->getLogPrefix() . 'Releasing locks...');
            $this->dataLayer->releaseMailerQueueLocksIfNeeded();
            $this->logger->logInfo($this->getLogPrefix() . 'Generating unique lock ID...');
            $lockId = $this->dataLayer->getUniqueLockId();
            $this->logger->logInfo($this->getLogPrefix() . 'Locking mails in queue...');
            $res = $this->dataLayer->lockMailsInQueue($lockId);
            if (empty($res)) {
                $this->logger->logError(
                    $this->getLogPrefix()
                    . 'Error locking mails in queue. Aborting...'
                );
                return;
            }
            $this->logger->logInfo($this->getLogPrefix() . 'Getting mails to process...');
            $mails = $this->dataLayer->getMailsFromQueue($lockId);

            /** @var MailerItem $mail */
            foreach ($mails as $mail) {
                $this->processMailItem($mail);
            }
        });
    }

    private function processMailItem(MailerItem $item): bool
    {
        $this->logger->logInfo(
            $this->getLogPrefix() . 'Building request for email ID: ' . $item->getId()
        );

        $emailReceivers = [new Email($item->getReceiverEmailAddress(), $item->getReceiverName())];
        $contentData = $this->requestBuilder->buildMailRequest(
            $emailReceivers,
            $item->getSubject(),
            $item->getContentHtml(),
            'text/html',
            $item->getSenderName()
        );
        $this->logger->logDebug($contentData);

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Logging API request for email ID: ' . $item->getId()
        );
        $newLogItemId = $this->logApiRequest($item->getId(), $contentData);

        $this->logger->logInfo($this->getLogPrefix() . 'Sending email ID: ' . $item->getId());
        $apiResponse = $this->apiClient->post(
            $this->getLogPrefix(),
            '/mail/send',
            $contentData
        );

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Logging API response for email ID: ' . $item->getId()
        );
        $this->logApiResponse($newLogItemId, $apiResponse);

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Marking email as processed ID: ' . $item->getId()
        );
        $res = $this->dataLayer->markMailAsProcessed($item, $apiResponse->hasError());

        if ($res) {
            $this->logger->logInfo($this->getLogPrefix() . 'Email sent ID: ' . $item->getId());
        } else {
            $this->logger->logError(
                $this->getLogPrefix() . 'Error sending email ID: ' . $item->getId()
            );
        }

        return $res;
    }

    private function logApiRequest(int $mailerQueueId, string $requestData): int
    {
        $newMailerItem = null;
        try {
            $newMailerItem = $this->dataLayer->storeMailerLog(
                new MailerLogItem(
                    null,
                    $mailerQueueId,
                    DateUtil::getCurrentDateForMySql(),
                    $requestData
                )
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging Mailer API request: ' . $t->getMessage());
            return 0;
        }

        return $newMailerItem->getId();
    }

    private function logApiResponse(int $newLogItemId, ApiResponse $res): void
    {
        try {
            $this->dataLayer->updateMailerLog(
                $newLogItemId,
                $res->getResponse(),
                $res->getErrorMessage(),
                !$res->hasError()
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                $this->getLogPrefix() .
                'Error updating API request - ID: ' . $newLogItemId .
                ' - Error: ' . $t->getMessage()
            );
        }
    }
}
