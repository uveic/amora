<?php

namespace Amora\Core\Module\Mailer\Model;

class Email
{
    private string $email;
    private ?string $name;

    public function __construct(
        string $email,
        ?string $name = null
    ) {
        $this->email = $email;
        $this->name = $name;
    }

    public static function fromArray(array $item): self
    {
        return new self(
            $item['email'],
            $item['name']
        );
    }

    public function asArray(): array
    {
        return [
            'email' => $this->getEmailAddress(),
            'name' => $this->getName()
        ];
    }

    public function getEmailAddress(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
