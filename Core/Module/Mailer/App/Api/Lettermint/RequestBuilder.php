<?php

namespace Amora\Core\Module\Mailer\App\Api\Lettermint;

use Amora\Core\Module\Mailer\App\Api\RequestBuilderAbstract;
use Amora\Core\Module\Mailer\Entity\Email;
use Amora\Core\Util\Logger;

class RequestBuilder extends RequestBuilderAbstract
{
    public function buildMailRequest(
        array $emailReceivers,
        string $subject,
        string $content,
        string $contentType = 'text/html;charset=UTF8',
        ?string $overwriteFromName = null,
        ?string $replyToEmail = null,
        ?string $replyToName = null,
    ): string {
        if (empty($emailReceivers)) {
            $this->logger->logError('Empty email receivers sending a Lettermint email. Aborting...');
            return '';
        }

        $receivers = [];
        /** @var Email $emailReceiver */
        foreach ($emailReceivers as $emailReceiver) {
            $receivers[] = $emailReceiver->email;
        }

        $from = $this->fromName ? ($this->fromName . ' <' . $this->fromEmail . '>') : $this->fromEmail;

        $contentData = [
            'route' => 'outgoing',
            'from' => $from,
            'to' => $receivers,
            'subject' => $subject,
            'html' => $content,
            'headers' => [
                'charset' => 'UTF-8',
                'Content-Type' => $contentType,
            ],
        ];

        if ($replyToEmail) {
            $contentData['reply_to'][] = $replyToEmail;
        }

        return json_encode($contentData, JSON_UNESCAPED_UNICODE);
    }
}