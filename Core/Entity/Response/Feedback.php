<?php

namespace Amora\Core\Entity\Response;

class Feedback
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly mixed $response = null,
        public readonly ?string $message = null,
        public readonly ?int $code = null
    ) {}
}
