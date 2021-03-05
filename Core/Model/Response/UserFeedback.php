<?php

namespace Amora\Core\Model\Response;

class UserFeedback
{
    public function __construct(
        private bool $isError,
        private ?string $message = null,
        private ?int $code = null
    ) {}

    public function asArray(): array
    {
        return [
            'has_error' => $this->isError(),
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ];
    }

    public function isError(): bool
    {
        return $this->isError;
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
