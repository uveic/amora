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

    public function getName(): string
    {
        return match ($this) {
            self::Spanish => 'Español',
            default => $this->name,
        };
    }

    public function getIconFlag(?string $class = null): string
    {
        $class = $class ? ' ' . $class : '';
        return match ($this) {
            self::English => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/EN.svg" alt="' . self::English->name . '">',
            self::Galego => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/GL.svg" alt="' . self::Galego->name . '">',
            self::Spanish => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/flags/ES.svg" alt="' . self::Spanish->name . '">',
        };
    }
}
