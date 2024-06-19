<?php

namespace Amora\Core\Module\Mailer\App\Api;

readonly class ApiResponse
{
    public function __construct(
        public string $response,
        public int $responseCode,
        public bool $hasError,
        public ?string $errorMessage = null
    ) {}
}
