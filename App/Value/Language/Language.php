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
}
