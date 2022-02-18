<?php

namespace Amora\Core\Module\Article\Value;

enum ArticleStatus: int
{
    case Published = 1;
    case Deleted = 2;
    case Draft = 3;
    case Private = 4;

    public static function getAll(): array
    {
        return [
            self::Published,
            self::Deleted,
            self::Draft,
            self::Private,
        ];
    }
}
