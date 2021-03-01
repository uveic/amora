<?php

namespace Amora\Core\Module\Mailer\App\Api;

use Amora\Core\Logger;

abstract class RequestBuilderAbstract
{
    private Logger $logger;
    private string $fromEmail;
    private string $fromName;
    private ?string $replyToEmail;
    private ?string $replyToName;

    public function __construct(
        Logger $logger,
        string $fromEmail,
        string $fromName,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ) {
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->replyToName = $replyToName;
    }

    abstract public function buildMailRequest(
        array $emailReceivers,
        string $subject,
        string $content,
        string $contentType = 'text/html',
        ?string $overwriteFromName = null
    ): string;

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->replyToEmail;
    }

    public function getReplyToName(): ?string
    {
        return $this->replyToName;
    }
}
