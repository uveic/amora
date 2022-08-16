<?php

namespace Amora\Core\Module\Mailer\App\Api;

use Amora\Core\Util\Logger;

abstract class RequestBuilderAbstract
{
    public function __construct(
        public readonly Logger $logger,
        public readonly string $fromEmail,
        public readonly string $fromName,
        public readonly ?string $replyToEmail = null,
        public readonly ?string $replyToName = null,
    ) {}

    abstract public function buildMailRequest(
        array $emailReceivers,
        string $subject,
        string $content,
        string $contentType = 'text/html',
        ?string $overwriteFromName = null,
    ): string;
}
