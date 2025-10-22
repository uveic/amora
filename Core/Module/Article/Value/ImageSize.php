<?php

namespace Amora\Core\Module\Article\Value;

enum ImageSize: int
{
    case XSmall = 250;
    case Small = 350;
    case Medium = 720;
    case Large = 1200;
    case XLarge = 1600;

    public function getSmaller(): ?self
    {
        return match ($this) {
            self::XSmall => null,
            self::Small => self::XSmall,
            self::Medium => self::Small,
            self::Large => self::Medium,
            self::XLarge => self::Large,
        };
    }

    public function getFilenameIdentifier(): string
    {
        return match ($this) {
            self::XSmall => 'xs',
            self::Small => 's',
            self::Medium => 'm',
            self::Large => 'l',
            self::XLarge => 'xl',
        };
    }
}
