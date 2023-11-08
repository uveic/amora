<?php

namespace Amora\Core\Module\Album\Value;

enum AlbumStatus: int
{
    case Published = 1;
    case Deleted = 2;
    case Draft = 3;
    case Private = 4;
    case Unlisted = 5;

    public static function getAll(): array
    {
        return [
            self::Published,
            self::Private,
            self::Unlisted,
            self::Draft,
            self::Deleted,
        ];
    }

    public function isPublic(): bool
    {
        return match ($this) {
            self::Published, self::Private, self::Unlisted => true,
            default => false,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Published => '<img draggable="false" class="img-svg m-r-025" width="20" height="20" src="/img/svg/checks-white.svg" alt="OK">',
            self::Deleted => '<img draggable="false" class="img-svg m-r-025" width="20" height="20" src="/img/svg/file-x-white.svg" alt="Trash">',
            self::Draft => '<img draggable="false" class="img-svg m-r-025" width="20" height="20" src="/img/svg/file-image-white.svg" alt="Processing">',
            self::Private => '<img draggable="false" class="img-svg m-r-025" width="20" height="20" src="/img/svg/check-white.svg" alt="Private">',
            self::Unlisted => '<img draggable="false" class="img-svg m-r-025" width="20" height="20" src="/img/svg/check-white.svg" alt="Unlisted">',
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::Published => 'Publicada',
            self::Deleted => 'Eliminada',
            self::Draft => 'Borrador',
            self::Private => 'Privado',
            self::Unlisted => 'No listado',
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::Published => 'status-published',
            self::Private, self::Unlisted => 'status-private',
            self::Deleted => 'status-deleted',
            self::Draft => 'status-draft',
        };
    }
}
