<?php

namespace Amora\Core\Database\Model;

class TransactionResponse
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly mixed $response = null,
        public readonly ?string $message = null,
    ) {}
}
