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

    public static function getInitialUserJourneyStatusFromRole(UserRole $role): self
    {
        return match ($role) {
            UserRole::Admin,
            UserRole::User => self::PendingPasswordCreation,
            default => self::Registration,
        };
    }
}
