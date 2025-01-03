<?php

namespace Amora\Core\Module\Article\Value;

use Amora\Core\Value\CoreIcons;

enum MediaType: int
{
    case PDF = 1;
    case Image = 2;
    case CSV = 3;
    case TXT = 4;

    case Unknown = 1000;

    public static function getAll(): array
    {
        return [
            self::PDF,
            self::Image,
            self::CSV,
            self::Unknown,
        ];
    }

    public static function getTypeFromRawFileType(?string $rawFileType): self
    {
        return match($rawFileType) {
            'image/jpeg', 'image/gif', 'image/png', 'image/webp' => self::Image,
            'application/pdf' => self::PDF,
            'text/csv' => self::CSV,
            'text/plain', 'text/plain;charset=UTF-8' => self::TXT,
            default => self::Unknown,
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::Image => CoreIcons::IMAGE,
            self::PDF => CoreIcons::FILE_PDF,
            self::CSV => CoreIcons::FILE_CSV,
            self::TXT => CoreIcons::FILE_TXT,
            default => CoreIcons::FILES,
        };
    }
}
