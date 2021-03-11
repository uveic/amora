<?php

namespace Amora\Core\Database\Model;

class TransactionResponse
{
    public function __construct(
        private bool $isSuccess,
        private mixed $response = null,
        private ?string $message = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
