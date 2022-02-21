<?php

namespace Amora\Core\Module\Mailer\Model;

class Email
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $name = null
    ) {}
}
