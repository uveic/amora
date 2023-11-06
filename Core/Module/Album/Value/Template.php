<?php

namespace Amora\Core\Module\Album\Value;

enum Template: int
{
    case NewYork = 1;

    public static function getAll(): array
    {
        return [
            self::NewYork,
        ];
    }

    public function getTemplate(): string
    {
        return match($this) {
            self::NewYork => 'new-york',
        };
    }
}
