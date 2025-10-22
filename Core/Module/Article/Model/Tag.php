<?php

namespace Amora\Core\Module\Article\Model;

class Tag
{
    public function __construct(
        public ?int $id,
        public readonly string $name
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['tag_id'],
            name: $data['name'],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
