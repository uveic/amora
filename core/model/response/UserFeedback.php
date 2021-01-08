<?php

namespace uve\core\model\response;

class UserFeedback
{
    private bool $isError;
    private ?string $message;
    private ?int $code;

    public function __construct(
        bool $isError,
        ?string $message,
        ?int $code = null
    ) {
        $this->isError = $isError;
        $this->message = $message;
        $this->code = $code;
    }

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
