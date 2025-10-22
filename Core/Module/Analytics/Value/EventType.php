<?php

namespace Amora\Core\Module\Analytics\Value;

enum EventType: int
{
    case Visitor = 1;
    case User = 2;
    case Bot = 3;
    case Api = 5;
    case Crawler = 6;

    public static function getAll(): array
    {
        return [
            self::Visitor,
            self::User,
            self::Bot,
            self::Api,
            self::Crawler,
        ];
    }

    public function deleteAfterProcessing(): bool
    {
        return match ($this) {
            self::Visitor, self::Bot, self::Crawler => true,
            default => false,
        };
    }
}
