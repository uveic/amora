<?php

namespace Amora\Core\Model\Util;

class LookupTableBasicValue
{
    public function __construct(
        private int $id,
        private string $name,
    ) {}

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName()
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
