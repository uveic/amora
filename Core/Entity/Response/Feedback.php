<?php

namespace Amora\Core\Entity\Response;

readonly class Feedback
{
    public function __construct(
        public bool $isSuccess,
        public mixed $response = null,
        public ?string $message = null,
        public ?int $code = null
    ) {
    }
}
