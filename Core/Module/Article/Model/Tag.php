<?php

namespace Amora\Core\Module\Article\Model;

class Tag
{
    public function __construct(
        private ?int $id,
        private string $name
    ) {}

    public static function fromArray(array $data): self
    {
        $id = isset($data['tag_id'])
            ? (int)$data['tag_id']
            : (empty($data['id']) ? null : (int)$data['id']);

        return new self(
            $id,
            $data['name']
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
