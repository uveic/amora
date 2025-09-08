<?php

namespace Amora\Core\Module\User\Value;

enum UserJourneyStatus: int
{
    case PendingPasswordCreation = 500;
    case RegistrationComplete = 1000;

    public static function getAll(): array
    {
        return [
            self::PendingPasswordCreation,
            self::RegistrationComplete,
        ];
    }

    public function getClassname(): string
    {
        return match ($this) {
            UserJourneyStatus::RegistrationComplete => 'status-published',
            UserJourneyStatus::PendingPasswordCreation => 'status-draft',
        };
    }
}
