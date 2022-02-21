<?php

namespace Amora\Core\Model\Response;

class UserFeedback
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly ?string $message = null,
        public readonly ?int $code = null
    ) {}
}
