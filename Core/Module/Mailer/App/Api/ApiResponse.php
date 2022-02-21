<?php

namespace Amora\Core\Module\Mailer\App\Api;

class ApiResponse
{
    public function __construct(
        public readonly string $response,
        public readonly int $responseCode,
        public readonly bool $hasError,
        public readonly ?string $errorMessage = null
    ) {}
}
