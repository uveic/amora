<?php

namespace Amora\Core\Module\Stats\Value;

class BotUserAgent
{
    public static array $agents = [
        'bingbot' => true,
        'WhatsApp' => true,
        'Googlebot' => true,
        'facebookexternalhit' => true,
        'Applebot' => true,
        'DuckDuckGo' => true,
        'YandexBot' => true,
        'Java' => true,
        'Go-http-client' => true,
        'Expanse' => true,
        'Dalvik' => true,
        'WordPress' => true,
        'got' => true,
        'DuckDuckBot-Https' => true,
        'Test' => true,
        'facebook-processing' => true,
        'GuzzleHttp' => true,
        'ALittle' => true,
        'cortex' => true,
        'IABot' => true,
        'IonCrawl' => true,
        'Validator' => true,
        'RepoLookoutBot' => true,
        'WebCopier' => true,
        'BUbiNG' => true,
        'httpx' => true,
        'W3C' => true,
        'Twitterbot' => true,
        'Googlebot-Image' => true,
        'OnalyticaBot' => true,
        'Pandalytics' => true,
    ];

    public static function isBot(string $item): bool
    {
        if (empty($item)) {
            return false;
        }

        return self::$agents[$item] ?? false;
    }
}
