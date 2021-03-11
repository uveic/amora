<?php

namespace Amora\Core\Model\Response;

class UserFeedback
{
    public function __construct(
        private bool $isSuccess,
        private ?string $message = null,
        private ?int $code = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }
}
