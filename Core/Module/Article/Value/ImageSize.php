<?php

namespace Amora\Core\Module\Article\Value;

enum ImageSize: int {
    case XSmall = 250;
    case Small = 350;
    case Medium = 720;
    case Large = 1200;
    case XLarge = 1600;

    public function getLarger(): ?self
    {
        return match ($this) {
            self::XSmall => self::Small,
            self::Small => self::Medium,
            self::Medium => self::Large,
            self::Large => self::XLarge,
            self::XLarge => null,
        };
    }
}
