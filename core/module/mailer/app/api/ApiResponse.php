<?php

namespace uve\core\module\mailer\app\api;

use uve\core\util\StringUtil;

class ApiResponse
{
    private string $response;
    private int $responseCode;
    private bool $hasError;
    private ?string $errorMessage;

    public function __construct(
        string $response,
        int $responseCode,
        bool $hasError,
        ?string $errorMessage = null
    ) {
        $this->response = $response;
        $this->responseCode = $responseCode;
        $this->hasError = $hasError;
        $this->errorMessage = $errorMessage;
    }

    public static function fromArray(array $item): self
    {
        return new self(
            $item['response'],
            StringUtil::isTrue($item['has_error'] ?? null),
            $item['error_message'] ?? null
        );
    }

    public function asArray(): array
    {
        return [
            'response' => $this->getResponse(),
            'has_error' => $this->hasError(),
            'error_message' => $this->getErrorMessage()
        ];
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
