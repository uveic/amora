<?php

namespace Amora\App\Value;

enum Language: string
{
    case English = 'EN';
    case Galego = 'GL';
    case Español = 'ES';

    public static function getAll(): array
    {
        return [
            self::English,
            self::Galego,
            self::Español,
        ];
    }

    public static function getIconFlag(Language $language, ?string $class = null): string
    {
        $class = $class ? ' ' . $class : '';
        return match ($language) {
            self::English => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/EN.svg" alt="' . self::English->name . '">',
            self::Galego => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/GL.svg" alt="' . self::Galego->name . '">',
            self::Español => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/ES.svg" alt="' . self::Español->name . '">',
        };
    }
}
