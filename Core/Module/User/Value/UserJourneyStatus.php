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
}
