<?php

namespace Amora\Core\Module\Mailer\Entity;

readonly class Email
{
    public function __construct(
        public string $email,
        public ?string $name = null
    ) {
    }
}
