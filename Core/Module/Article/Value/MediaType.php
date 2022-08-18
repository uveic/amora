<?php

namespace Amora\Core\Module\Article\Value;

enum MediaType: int
{
    case PDF = 1;
    case Image = 2;
    case Unknown = 3;

    public static function getAll(): array
    {
        return [
            self::PDF,
            self::Image,
            self::Unknown,
        ];
    }

    public static function getTypeFromRawFileType(?string $rawFileType): self
    {
        return match($rawFileType) {
            'image/jpeg', 'image/gif', 'image/png', 'image/webp' => self::Image,
            'application/pdf' => self::PDF,
            default => self::Unknown,
        };
    }
}
