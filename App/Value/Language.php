<?php

namespace Amora\App\Value;

enum Language: string
{
    case English = 'EN';
    case Galego = 'GL';
    case Spanish = 'ES';

    public static function getAll(): array
    {
        return [
            self::English,
            self::Galego,
            self::Spanish,
        ];
    }

    public static function getActive(): array
    {
        return self::getAll();
    }

    public function getName(): string
    {
        return match ($this) {
            self::Spanish => 'Español',
            default => $this->name,
        };
    }

    public function getIconFlag(?string $class = null, bool $lazyLoading = true): string
    {
        $class = $class ? ' ' . $class : '';
        $loadingString = $lazyLoading ? ' loading="lazy"' : '';

        return'<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/' . $this->value . '.svg" alt="' . $this->getName() . '"' . $loadingString . ' draggable="false">';
    }

    public function getLocale(): string
    {
        return match ($this) {
            self::English => 'en_GB',
            self::Galego => 'gl_ES',
            self::Spanish => 'es_ES',
        };
    }

    public static function getLanguagePriority(): array
    {
        return [
            self::English,
            self::Spanish,
            self::Galego,
        ];
    }
}
