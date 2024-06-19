<?php

namespace Amora\Core\Module\Mailer\App\Api\Brevo;

use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\Entity\Email;
use Amora\Core\Util\Logger;

class RequestBuilder extends RequestBuilderAbstract
{
    public function __construct(
        Logger $logger,
        string $fromEmail,
        string $fromName,
    ) {
        parent::__construct(
            logger: $logger,
            fromEmail: $fromEmail,
            fromName: $fromName,
        );
    }

    public function buildMailRequest(
        array $emailReceivers,
        string $subject,
        string $content,
        string $contentType = 'text/html',
        ?string $overwriteFromName = null,
        ?string $replyToEmail = null,
        ?string $replyToName = null,
    ): string {
        if (empty($emailReceivers)) {
            $this->logger->logError('Empty email receivers sending a Brevo email. Aborting...');
            return '';
        }

        $receivers = [];
        /** @var Email $emailReceiver */
        foreach ($emailReceivers as $emailReceiver) {
            $new = [
                'email' => $emailReceiver->email
            ];

            if ($emailReceiver->name) {
                $new['name'] = $emailReceiver->name;
            }

            $receivers[] = $new;
        }

        $contentData = [
            'sender' => [
                'email' => $this->fromEmail,
                'name' => $overwriteFromName ?? $this->fromName,
            ],
            'to' => $receivers,
            'subject' => $subject,
            'htmlContent' => $content,
            'headers' => [
                'charset' => 'UTF-8',
                'Content-Type' => 'text/html;charset=UTF8',
            ],
        ];

        if ($replyToEmail) {
            $replyToData = [
                'email' => $replyToEmail
            ];

            if ($replyToName) {
                $replyToData['name'] = $replyToName;
            }

            $contentData['replyTo'] = $replyToData;
        }

        return json_encode($contentData, JSON_UNESCAPED_UNICODE);
    }
}