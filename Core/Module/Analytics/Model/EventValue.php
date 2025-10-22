<?php

namespace Amora\Core\Module\Analytics\Model;

class EventValue
{
    public function __construct(
        public ?int $id,
        public readonly string $value,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['event_value_id'],
            value: $data['event_value_value'],
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
        ];
    }
}
