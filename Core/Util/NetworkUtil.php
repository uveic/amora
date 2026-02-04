<?php

namespace Amora\Core\Util;

final readonly class NetworkUtil
{
//    /**
//     * @var Reader Cached Reader instance for IP Country lookups
//     */
//    private static Reader $geoliteCountryReader;
//
//    /**
//     * @var Reader Cached Reader instance for IP City lookups
//     */
//    private static Reader $geoliteCityReader;

    /**
     * Takes an array of HTTP request headers, and works out the IP of
     * the connecting client.
     *
     * @return string|null The client IP
     */
    public static function determineClientIp(): ?string
    {
        $ipString = null;

        if (!empty($_SERVER['HTTP_INCAP_CLIENT_IP'])) {
            $ipString = $_SERVER['HTTP_INCAP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipString = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipString = $_SERVER['REMOTE_ADDR'];
        }

        if (empty($ipString)) {
            return $ipString;
        }

        $requestIp = '';
        preg_match_all('(\d{1,3}\\.\d{1,3}\\.\d{1,3}\\.\d{1,3})', $ipString, $matches);

        if (!empty($matches)) {
            foreach ($matches[0] as $ip) {
                // Return the first IP that's not in the private ranges below:
                //
                // 127.0.0.1
                // 10.0.0.0 – 10.255.255.255
                // 172.16.0.0 – 172.31.255.255
                // 192.168.0.0 – 192.168.255.255
                if (0 === preg_match('/(^127\\.0\\.0\\.1)|(^10\\.)|(^172\\.1[6-9]\\.)|(^172\\.2\d\\.)|(^172\\.3[0-1]\\.)|(^192\\.168\\.)/', $ip)) {
                    $requestIp = $ip;
                    break;
                }
            }
        }

        return $requestIp;
    }
//
//    /**
//     * Use the GeoLite2 Country DB to find the country for a given IP address. If an IP is not provided,
//     * the value is looked up using `determineClientIp()` above.
//     *
//     * @param string|null $ip Optional IP address to look up the country for
//     * @return string|null The two-letter ISO code for the relevant IP if one can be found, empty String otherwise
//     */
//    public static function getCountryCodeFromIP(?string $ip = null): ?string
//    {
//        if (empty($ip)) {
//            return null;
//        }
//
//        $countryCode = null;
//        try {
//            if (!isset(self::$geoliteCountryReader)) {
//                self::$geoliteCountryReader = new Reader(
//                    Core::getPathRoot() . '/vendor/GeoLite2-Country.mmdb'
//                );
//            }
//
//            $record = self::$geoliteCountryReader->country($ip);
//            $countryCode = $record->country->isoCode;
//        } catch (Throwable) {}
//
//        return $countryCode;
//    }
//
//    /**
//     * Use the GeoLite2 City DB to find the city for a given IP address. If an IP is not provided,
//     * the value is looked up using `determineClientIp()` above.
//     *
//     * @param string|null $ip Optional IP address to look up the city for
//     * @return string|null
//     */
//    public static function getCityFromIP(?string $ip = null): ?string
//    {
//        if (empty($ip)) {
//            return null;
//        }
//
//        $city = null;
//        try {
//            if (empty(self::$geoliteCityReader)) {
//                self::$geoliteCityReader = new Reader(
//                    Core::getPathRoot() . '/vendor/GeoLite2-City.mmdb'
//                );
//            }
//
//            $record = self::$geoliteCityReader->city($ip);
//            $city = $record->city->name;
//        } catch (Throwable) {}
//
//        return $city;
//    }
}
