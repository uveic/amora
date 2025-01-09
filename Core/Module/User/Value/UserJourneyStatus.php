<?php

namespace Amora\Core\Module\User\Value;

enum UserJourneyStatus: int
{
    case PendingPasswordCreation = 500;
    case Registration = 1000;

    public static function getAll(): array
    {
        return [
            self::PendingPasswordCreation,
            self::Registration,
        ];
    }

    public function getClassname(): string
    {
        return match ($this) {
            UserJourneyStatus::Registration => 'status-published',
            UserJourneyStatus::PendingPasswordCreation => 'status-draft',
        };
    }
}
