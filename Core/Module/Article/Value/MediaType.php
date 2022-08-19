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

    public static function getIcon(self $item, string $class = ''): string
    {
        $class = $class ? ' ' . $class : '';
        return match($item) {
            self::Image => '<img class="img-svg' . $class . '" width="20" height="20" src="/img/svg/image-black.svg" alt="Img">',
            self::PDF => '<img src="/img/svg/file-pdf.svg" class="img-svg' . $class . '" alt="PDF">',
            default => '<img src="/img/svg/files.svg" class="img-svg' . $class . '" alt="File">',
        };
    }
}
