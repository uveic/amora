<?php

namespace Amora\App\Value;

enum Language: string
{
    case English = 'EN';
    case Galego = 'GL';

    public static function getAll(): array
    {
        return [
            self::English,
            self::Galego,
        ];
    }

    public static function getIconFlag(Language $language, ?string $class = null): string
    {
        $class = $class ? ' ' . $class : '';
        return match ($language) {
            self::English => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/EN.svg" alt="' . self::English->name . '">',
            self::Galego => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/GL.svg" alt="' . self::Galego->name . '">',
        };
    }
}
