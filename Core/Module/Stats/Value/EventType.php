<?php

namespace Amora\Core\Module\Stats\Value;

enum EventType: int
{
    case Visitor = 1;
    case User = 2;
    case Bot = 3;
    case ProbablyBot = 4;
    case Api = 5;

    case Unknown = 100;

    public static function getAll(): array
    {
        return [
            self::Visitor,
            self::User,
            self::Bot,
            self::ProbablyBot,
            self::Api,
            self::Unknown,
        ];
    }
}
