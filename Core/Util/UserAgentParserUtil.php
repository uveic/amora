<?php

namespace Amora\Core\Util;

use Amora\Core\Entity\Util\UserAgentInfo;
use Random\RandomException;

final readonly class UserAgentParserUtil
{
    public const string PLATFORM = 'platform';
    public const string BROWSER  = 'browser';
    public const string BROWSER_VERSION = 'version';

    /**
     * Parses a user agent string into its important parts
     *
     * @param string|null $userAgent
     * @return UserAgentInfo
     *
     * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
     * @author Jesse G. Donat <donatj@gmail.com>
     * @link https://github.com/donatj/PhpUserAgent
     */
    public static function parse(?string $userAgent): UserAgentInfo
    {
        $platform = '';

        if (!$userAgent) {
            return new UserAgentInfo();
        }

        if (preg_match('/\((.*?)\)/m', $userAgent, $parent_matches)) {
            preg_match_all(<<<'REGEX'
/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
(?:\ [^;]*)?
(?:;|$)/imx
REGEX
                , $parent_matches[1], $result);

            $priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'FreeBSD', 'NetBSD', 'OpenBSD', 'CrOS', 'X11' );

            $result[self::PLATFORM] = array_unique($result[self::PLATFORM]);
            if (count($result[self::PLATFORM]) > 1) {
                if ($keys = array_intersect($priority, $result[self::PLATFORM])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result[self::PLATFORM][0];
                }
            } elseif (isset($result[self::PLATFORM][0])) {
                $platform = $result[self::PLATFORM][0];
            }
        }

        if ($platform === 'linux-gnu' || $platform === 'X11') {
            $platform = 'Linux';
        } elseif ($platform === 'CrOS') {
            $platform = 'Chrome OS';
        }

        preg_match_all(<<<'REGEX'
%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
TizenBrowser|(?:Headless)?Chrome|YaBrowser|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|Edg|CriOS|UCBrowser|Puffin|OculusBrowser|SamsungBrowser|
Baiduspider|Applebot|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
Valve\ Steam\ Tenfoot|Thunderbird|
NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
\)?;?
(?:[:/ ](?P<version>[0-9A-Z.]+)|/[A-Z]*)
%ix
REGEX
            , $userAgent, $result);

        // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result[self::BROWSER][0], $result[self::BROWSER_VERSION][0])) {
            if (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $userAgent, $result)) {
                return new UserAgentInfo(
                    platform: $platform ?: null,
                    browser: $result[self::BROWSER],
                    version: empty($result[self::BROWSER_VERSION])
                        ? null
                        : $result[self::BROWSER_VERSION],
                );
            }

            return new UserAgentInfo();
        }

        if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/i', $userAgent, $rv_result)) {
            $rv_result = $rv_result[self::BROWSER_VERSION];
        }

        $browser = $result[self::BROWSER][0];
        $version = $result[self::BROWSER_VERSION][0];

        $lowerBrowser = array_map('strtolower', $result[self::BROWSER]);

        $find = static function ($search, &$key = null, &$value = null) use ($lowerBrowser) {
            $search = (array)$search;

            foreach ($search as $val) {
                $xKey = array_search(strtolower($val), $lowerBrowser, true);
                if ($xKey !== false) {
                    $value = $val;
                    $key   = $xKey;

                    return true;
                }
            }

            return false;
        };

        $findT = static function (array $search, &$key = null, &$value = null) use ($find) {
            $value2 = null;
            if ($find(array_keys($search), $key, $value2)) {
                $value = $search[$value2];

                return true;
            }

            return false;
        };

        $key = 0;
        $val = '';
        if ($findT(array( 'OPR' => 'Opera', 'UCBrowser' => 'UC Browser', 'YaBrowser' => 'Yandex', 'Iceweasel' => 'Firefox', 'Icecat' => 'Firefox', 'CriOS' => 'Chrome', 'Edg' => 'Edge' ), $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Playstation Vita', $key, $platform)) {
            $platform = 'PlayStation Vita';
            $browser  = 'Browser';
        } elseif ($find(array( 'Kindle Fire', 'Silk' ), $key, $val)) {
            $browser  = $val === 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result[self::BROWSER_VERSION][$key]) || !is_numeric($version[0])) {
                $version = $result[self::BROWSER_VERSION][array_search('Version', $result[self::BROWSER], true)];
            }
        } elseif ($platform === 'Nintendo 3DS' || $find('NintendoBrowser', $key)) {
            $browser = 'NintendoBrowser';
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Kindle', $key, $platform)) {
            $browser = $result[self::BROWSER][$key];
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Opera', $key, $browser)) {
            $find('Version', $key);
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Puffin', $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
            if (strlen($version) > 3) {
                $part = substr($version, -2);
                if (ctype_upper($part)) {
                    $version = substr($version, 0, -2);

                    $flags = array( 'IP' => 'iPhone', 'IT' => 'iPad', 'AP' => 'Android', 'AT' => 'Android', 'WP' => 'Windows Phone', 'WT' => 'Windows' );
                    if (isset($flags[$part])) {
                        $platform = $flags[$part];
                    }
                }
            }
        } elseif ($find(array( 'Applebot', 'IEMobile', 'Edge', 'Midori', 'Vivaldi', 'OculusBrowser', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome', 'HeadlessChrome' ), $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($rv_result && $find('Trident')) {
            $browser = 'MSIE';
            $version = $rv_result;
        } elseif ($browser === 'AppleWebKit') {
            if ($platform === 'Android') {
                $browser = 'Android Browser';
            } elseif (str_starts_with($platform, 'BB')) {
                $browser  = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform === 'BlackBerry' || $platform === 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }

            $find('Version', $key);
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($pKey = preg_grep('/playstation \d/i', $result[self::BROWSER])) {
            $pKey = reset($pKey);

            $platform = 'PlayStation ' . preg_replace('/\D/', '', $pKey);
            $browser  = 'NetFront';
        }

        return new UserAgentInfo(
            $platform ?: null,
            $browser ?: null,
            $version ?: null
        );
    }

    public static function getBrowserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
        ];

        try {
            return $agents[random_int(0, count($agents) - 1)];
        } catch (RandomException) {
            return $agents[0];
        }
    }
}
