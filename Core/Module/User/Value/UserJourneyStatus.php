<?php

namespace Amora\Core\Module\User\Value;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Value\CoreIcons;

enum UserJourneyStatus: int
{
    case PendingPasswordCreation = 500;
    case PendingEmailVerification = 501;
    case RegistrationComplete = 1000;

    public static function getAll(): array
    {
        return [
            self::PendingPasswordCreation,
            self::PendingEmailVerification,
            self::RegistrationComplete,
        ];
    }

    public function getClassname(): string
    {
        return match ($this) {
            self::RegistrationComplete => 'status-published',
            default => 'status-draft',
        };
    }

    public function getTitle(Language $language): string {
        return Core::getLocalisationUtil($language)->getValue('userJourney' . $this->name);
    }

    public function getVerificationType(): ?VerificationType
    {
        return match ($this) {
            self::PendingPasswordCreation => VerificationType::PasswordCreation,
            self::PendingEmailVerification => VerificationType::VerifyEmailAddress,
            default => null,
        };
    }

    public function asHtml(Language $language): string
    {
        return '<span class="article-status ' .
            $this->getClassname() . '">' .
            CoreIcons::INFO .
            $this->getTitle($language) .
            '</span>';
    }
}
