<?php

namespace Amora\Core\Module\Mailer\App\Api\Sendgrid;

use Amora\Core\Logger;
use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\Model\Email;

class RequestBuilder extends RequestBuilderAbstract
{
    public function __construct(
        Logger $logger,
        string $fromEmail,
        string $fromName,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ) {
        parent::__construct($logger, $fromEmail, $fromName, $replyToEmail, $replyToName);
    }

    public function buildMailRequest(
        array $emailReceivers,
        string $subject,
        string $content,
        string $contentType = 'text/html',
        ?string $overwriteFromName = null
    ): string {
        if (empty($emailReceivers)) {
            $this->getLogger()->logError('Empty email receivers when sending a Sendgrid email');
            return '';
        }

        $receivers = [];
        /** @var Email $emailReceiver */
        foreach ($emailReceivers as $emailReceiver) {
            $new = [
                'email' => $emailReceiver->getEmailAddress()
            ];

            if ($emailReceiver->getName()) {
                $new['name'] = $emailReceiver->getName();
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
                'email' => $this->getFromEmail(),
                'name' => $overwriteFromName ?? $this->getFromName()
            ],
            'content' => [
                [
                    'type' => $contentType,
                    'value' => $content
                ]
            ]
        ];

        if ($this->getReplyToEmail()) {
            $replyToData = [
                'email' => $this->getReplyToEmail()
            ];

            if ($this->getReplyToName()) {
                $replyToData['name'] = $this->getReplyToName();
            }

            $contentData['reply_to'] = $replyToData;
        }

        return json_encode($contentData);
    }
}
