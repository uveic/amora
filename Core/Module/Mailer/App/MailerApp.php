<?php

namespace Amora\Core\Module\Mailer\App;

use Amora\Core\Core;
use DateTimeImmutable;
use Throwable;
use Amora\Core\App\App;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\App\Api\ApiResponse;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\Datalayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Model\Email;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Model\MailerLogItem;

class MailerApp extends App
{
    public function __construct(
        Logger $logger,
        private MailerDataLayer $dataLayer,
        private ApiClientAbstract $apiClient,
        private RequestBuilderAbstract $requestBuilder,
        bool $isPersistent = true,
    ) {
        parent::__construct(
            logger: $logger,
            appName: Core::getConfig()->appName . 'MailerApp',
            isPersistent: $isPersistent,
        );
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

    public function processMailItem(MailerItem $item): bool
    {
        $this->logger->logInfo(
            $this->getLogPrefix() . 'Building request for email ID: ' . $item->id
        );

        $emailReceivers = [new Email($item->receiverEmailAddress, $item->receiverName)];
        $contentData = $this->requestBuilder->buildMailRequest(
            emailReceivers: $emailReceivers,
            subject: $item->subject,
            content: $item->contentHtml,
            contentType: 'text/html',
            overwriteFromName: $item->senderName,
        );
        $this->logger->logDebug($contentData);

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Logging API request for email ID: ' . $item->id
        );
        $newLogItemId = $this->logApiRequest($item->id, $contentData);

        $this->logger->logInfo($this->getLogPrefix() . 'Sending email ID: ' . $item->id);
        $apiResponse = Core::isRunningInLiveEnv()
            ? $this->apiClient->post(
                $this->getLogPrefix(),
                '/mail/send',
                $contentData
            )
            : new ApiResponse(
                response: 'DevEnvironment: sent',
                responseCode: 200,
                hasError: false,
            );

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Logging API response for email ID: ' . $item->id
        );
        $this->logApiResponse($newLogItemId, $apiResponse);

        $this->logger->logInfo(
            $this->getLogPrefix() . 'Marking email as processed ID: ' . $item->id
        );
        $res = $this->dataLayer->markMailAsProcessed($item, $apiResponse->hasError);

        if ($res) {
            $this->logger->logInfo($this->getLogPrefix() . 'Email sent ID: ' . $item->id);
        } else {
            $this->logger->logError(
                $this->getLogPrefix() . 'Error sending email ID: ' . $item->id
            );
        }

        return $res;
    }

    private function logApiRequest(int $mailerQueueId, string $requestData): int
    {
        try {
            $newMailerItem = $this->dataLayer->storeMailerLog(
                new MailerLogItem(
                    id: null,
                    mailerQueueId: $mailerQueueId,
                    createdAt: new DateTimeImmutable(),
                    request: $requestData,
                )
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging Mailer API request: ' . $t->getMessage());
            return 0;
        }

        return $newMailerItem->id;
    }

    private function logApiResponse(int $newLogItemId, ApiResponse $res): void
    {
        try {
            $this->dataLayer->updateMailerLog(
                id: $newLogItemId,
                response: $res->response,
                errorMessage: $res->errorMessage,
                sent: !$res->hasError,
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
