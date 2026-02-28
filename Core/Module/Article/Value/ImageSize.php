<?php

namespace Amora\Core\Module\Article\Value;

enum ImageSize: int
{
    case XSmall = 250;
    case Small = 350;
    case Medium = 720;
    case Large = 1200;
    case XLarge = 1600;
//    case X2Large = 2400;
//    case X3Large = 3200;

    public function getSmaller(): ?self
    {
        return match ($this) {
            self::XSmall => null,
            self::Small => self::XSmall,
            self::Medium => self::Small,
            self::Large => self::Medium,
            self::XLarge => self::Large,
//            self::X2Large => self::XLarge,
//            self::X3Large => self::X2Large,
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
//            self::X2Large => '2xl',
//            self::X3Large => '3xl',
        };
    }
}
