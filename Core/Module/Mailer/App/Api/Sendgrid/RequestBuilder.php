<?php

namespace Amora\Core\Module\Mailer\App\Api\Sendgrid;

use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\Entity\Email;
use Amora\Core\Util\Logger;

class RequestBuilder extends RequestBuilderAbstract
{
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
            $this->logger->logError('Empty email receivers when sending a Sendgrid email');
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
            'personalizations' => [
                [
                    'to' => array_values($receivers),
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $this->fromEmail,
                'name' => $overwriteFromName ?? $this->fromName,
            ],
            'content' => [
                [
                    'type' => $contentType,
                    'value' => $content,
                ]
            ],
        ];

        if ($replyToEmail) {
            $replyToData = [
                'email' => $replyToEmail
            ];

            if ($replyToName) {
                $replyToData['name'] = $replyToName;
            }

            $contentData['reply_to'] = $replyToData;
        }

        return json_encode($contentData, JSON_UNESCAPED_UNICODE);
    }
}
