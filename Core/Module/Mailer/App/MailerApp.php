<?php

namespace Amora\Core\Module\Mailer\App;

use Amora\Core\App\App;
use Amora\Core\Core;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\ApiResponse;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\DataLayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Entity\Email;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Model\MailerLogItem;
use Amora\Core\Util\Logger;
use DateTimeImmutable;
use Throwable;

class MailerApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MailerDataLayer $dataLayer,
        private readonly ApiClientAbstract $apiClient,
        private readonly RequestBuilderAbstract $requestBuilder,
        bool $isPersistent = true,
    ) {
        parent::__construct(
            logger: $logger,
            appName: Core::getConfig()->appName . 'MailerApp',
            isPersistent: $isPersistent,
        );
    }

    public function run(): void {
        $this->execute(function () {
            $this->log('Releasing locks...');
            $this->dataLayer->releaseMailerQueueLocksIfNeeded();
            $this->log('Generating unique lock ID...');
            $lockId = $this->dataLayer->getUniqueLockId();
            $this->log('Locking mails in queue...');
            $res = $this->dataLayer->lockMailsInQueue($lockId);
            if (empty($res)) {
                $this->log('Error locking mails in queue. Aborting...', true);
                return;
            }
            $this->log('Getting mails to process...');
            $mails = $this->dataLayer->getMailsFromQueue($lockId);

            /** @var MailerItem $mail */
            foreach ($mails as $mail) {
                $this->processMailItem($mail);
            }
        });
    }

    public function processMailItem(MailerItem $item): bool
    {
        $this->log('Building request for email ID: ' . $item->id);

        $emailReceivers = [new Email($item->receiverEmailAddress, $item->receiverName)];
        $contentData = $this->requestBuilder->buildMailRequest(
            emailReceivers: $emailReceivers,
            subject: $item->subject,
            content: $item->contentHtml,
            overwriteFromName: $item->senderName,
            replyToEmail: $item->replyToEmailAddress,
        );
        $this->logger->logDebug($contentData);

        $this->log('Logging API request for email ID: ' . $item->id);
        $newLogItemId = $this->logApiRequest($item->id, $contentData);

        $this->log('Sending email ID: ' . $item->id);
        $apiResponse = Core::isRunningInLiveEnv()
            ? $this->apiClient->sendEmail(
                logPrefix: $this->getLogPrefix(),
                jsonData: $contentData,
            )
            : new ApiResponse(
                response: 'DevEnvironment: sent',
                responseCode: 200,
                hasError: false,
            );

        $this->log('Logging API response for email ID: ' . $item->id);
        $this->logApiResponse($newLogItemId, $apiResponse);

        $this->log('Marking email as processed ID: ' . $item->id);
        $res = $this->dataLayer->markMailAsProcessed($item, $apiResponse->hasError);

        if ($res) {
            $this->log('Email sent ID: ' . $item->id);
        } else {
            $this->log('Error sending email ID: ' . $item->id, true);
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
            $this->log('Error updating API request - ID: ' . $newLogItemId . ' - Error: ' . $t->getMessage(), true);
        }
    }
}
