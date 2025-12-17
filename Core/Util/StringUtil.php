<?php

namespace Amora\Core\Util;

use Amora\App\Router\AppRouter;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Throwable;

final class StringUtil
{
    public static function cleanString(
        string $text,
        string $charsToBeRemoved = '',
        string $replaceWith = '-',
        bool $keepSpaces = false,
    ): string {
        if (!empty($charsToBeRemoved)) {
            foreach (str_split($charsToBeRemoved) as $item) {
                $text = str_replace($item, $replaceWith, $text);
            }
        }

        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/["\']/u'      =>   ' ', // Quotes
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );
        $text = preg_replace(array_keys($utf8), array_values($utf8), $text);

        $text = preg_replace('/[^A-Za-z0-9\s-]+/', $replaceWith, $text);

        if ($keepSpaces) {
            $text = preg_replace('/\s+/', ' ', $text);
        } else {
            $text = preg_replace('/\s+/', $replaceWith, $text);
        }

        $text = preg_replace('/' . $replaceWith . '+/', $replaceWith, $text);

        return trim($text, ' ' . $replaceWith);
    }

    public static function sanitiseText(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitiseHtml(?string $html): ?string
    {
        return $html;
//        if (empty($html)) {
//            return $html;
//        }
//
//        if (!self::$antiXss) {
//            self::$antiXss = new AntiXSS();
//        }
//
//        return self::$antiXss->xss_clean($html);
    }

    /**
     * Check if $value is true
     *
     * This is true: "1", 1, true, "true"
     * This is false: "11", 11, "0", "hello", ...
     *
     * @param int|bool|string $value
     * @return bool
     */
    public static function isTrue(mixed $value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        if (is_string($value)) {
            return strtolower($value) === 'true';
        }

        return false;
    }

    public static function utf8Split(string $string, int $length = 1): array
    {
        $output = [];
        $strLen = mb_strlen($string, 'UTF-8');

        for ($i = 0; $i < $strLen; $i++) {
            $output[] = mb_substr($string, $i, $length, 'UTF-8');
        }

        return $output;
    }

    public static function generateRandomString(int $length = 24, bool $onlyChars = false): string
    {
        $characters = ($onlyChars ? '' : '0123456789') . 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(
            str_shuffle(
                str_repeat(
                    $characters,
                    ceil($length / strlen($characters))
                )
            ),
            1,
            $length
        );
    }

    public static function generateNonce(): string
    {
        return base64_encode(self::generateRandomString());
    }

    public static function generateFormValidator(string $value): string
    {
        $salt = Core::getConfig()->salt;
        return md5($value . $salt);
    }

    public static function validateForm(string $formValidator, string $value): bool
    {
        $salt = Core::getConfig()->salt;
        return $formValidator === md5($value . $salt);
    }

    public static function isEmailAddressValid(string $emailAddress): bool
    {
        return !empty(filter_var($emailAddress, FILTER_VALIDATE_EMAIL));
    }

    public static function hashPassword(string $pass): string
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $unHashedPassword, string $hashedPassword): bool
    {
        return password_verify($unHashedPassword, $hashedPassword);
    }

    public static function normaliseEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function stripHtmlTags(string $text): string
    {
        $text = str_replace(['<br>', '<br/>'], ' ', $text);
        return strip_tags($text);
    }

    public static function formatNumber(
        Language $language,
        float $number,
        int $decimals = 0,
        bool $includeThousandsSeparator = true,
    ): string {
        if ($language === Language::Spanish || $language === Language::Galego) {
            $thousandsSeparator = $includeThousandsSeparator ? '.' : '';
            $decimalsSeparator = ',';
        } else {
            $thousandsSeparator = $includeThousandsSeparator ? ',' : '';
            $decimalsSeparator = '.';
        }

        return number_format($number, $decimals, $decimalsSeparator, $thousandsSeparator);
    }

    public static function filterCommaSeparatedIntegers(?string $commaSeparatedValues): array
    {
        if (empty($commaSeparatedValues)) {
            return [];
        }

        $values = explode(',', $commaSeparatedValues);
        return array_filter($values, static function ($value) {
            return is_numeric($value);
        });
    }

    public static function getTitleFromRemoteUrl(string $url): ?string
    {
        try {
            $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

            $response = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                return null;
            }

            if ($responseCode < 200 || $responseCode > 299) {
                return null;
            }

            $start = strpos($response, '<title>');
            $end = strpos($response, '</title>');

            if ($start === false || $end === false) {
                return null;
            }

            $start += 7;
            $title = trim(substr($response, $start, $end - $start));

            if (str_contains(strtolower($response), 'iso-8859-1')) {
                $utf8EncodedTitle = iconv("ISO-8859-1", "UTF-8", $title);
                $title = $utf8EncodedTitle !== false ? $utf8EncodedTitle : $title;
            }

            if (strlen($title) <= 0) {
                return null;
            }

            return htmlentities($title);
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Error retrieving title for URL: ' . $url
                . ' - Error message: ' . $t->getMessage()
            );
            return null;
        }
    }

    public static function encodeURIComponent(string $str): string
    {
        $revert = [
            '%21' => '!',
            '%2A' => '*',
            '%27' => "'",
            '%28' => '(',
            '%29' => ')',
        ];

        return strtr(rawurlencode($str), $revert);
    }

    // Converts newline to HTML paragraph: <p>
    // Ignores it if it already has HTML paragraphs
    public static function nl2p(?string $str): string
    {
        if (empty($str)) {
            return '';
        }

        if (str_contains($str, '<p>')) {
            return $str;
        }

        $paragraphs = '';

        foreach (explode("\n", $str) as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $paragraphs .= '<p>' . $line . '</p>';
        }

        return $paragraphs;
    }

    public static function makeLinksClickable(string $str): string
    {
        if (str_contains($str, 'href')) {
            return $str;
        }

        $url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s.]+[^\s]*)+[^,.\s])@';
        return preg_replace($url, '<a href="http$2://$4" title="$0">$0</a>', $str);
    }

    public static function removeHttpFromLink(string $text): string
    {
        return str_replace(['http://', 'https://'], '', $text);
    }

    public static function getFirstParagraphAsPlainText(
        ?string $text,
        int $maxLength = 400,
        bool $trimToFirstSentence = false,
    ): string {
        if (!$text) {
            return '';
        }

        $text = str_replace('&nbsp;', ' ', $text);
        $position = strpos($text, '</p>');
        if ($position) {
            $firstParagraph = trim(strip_tags(substr($text, 0, $position)));

            if (strlen($firstParagraph) > $maxLength) {
                if ($trimToFirstSentence) {
                    $position = strpos($firstParagraph, '. ');
                    if ($position) {
                        return trim(substr($firstParagraph, 0, $position)) . '.';
                    }
                }

                return trim(substr($firstParagraph, 0, $maxLength), ' .') . '...';
            }

            return $firstParagraph;
        }

        $text = trim(strip_tags($text));

        if (strlen($text) > $maxLength) {
            return trim(substr($text, 0, $maxLength), ' .') . '...';
        }

        return $text;
    }

    public static function generateSlug(?string $text = null, int $length = 64): string
    {
        $slug = $text
            ? strtolower(self::cleanString($text))
            : strtolower(self::generateRandomString($length));

        $count = 0;
        do {
            $validSlug = $slug . ($count > 0 ? '-' . $count : '');
            $res = in_array($validSlug, AppRouter::getReservedPaths(), true);
            $count++;
        } while ($res);

        return $validSlug;
    }

    public static function getFirstWord(?string $text, int $minLength = 4): string
    {
        if (!$text) {
            return '';
        }

        $tok = strtok($text, ' ');
        while ($tok !== false) {
            if (strlen($tok) >= $minLength) {
                return $tok;
            }

            $tok = strtok(' ');
        }

        return '';
    }

    public static function cleanSearchQuery(string $searchQuery, array $wordsToRemove = []): string
    {
        $originalQuery = $searchQuery;

        foreach ($wordsToRemove as $word) {
            $searchQuery = trim(str_ireplace($word, '', $searchQuery));
        }

        if (!$originalQuery) {
            $searchQuery = trim($originalQuery);
        }

        $fulltextParts = [];
        $parts = explode(' ', $searchQuery);
        foreach ($parts as $part) {
            if (strlen(trim($part)) >= 3) {
                $fulltextParts[] = $part;
            }
        }

        return $fulltextParts ? implode(' ', $fulltextParts) : $searchQuery;
    }

    public static function convertToUtf8(string|array|null $d): string
    {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = self::convertToUtf8($v);
            }
        } elseif (is_string($d)) {
            return mb_convert_encoding($d, 'UTF-8', 'ISO-8859-1');
        }

        return $d;
    }

    public static function getYoutubeVideoIdFromUrl(string $url): ?string
    {
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches);
        return $matches[0] ?? null;
    }

    public static function buildYoutubeThumbnailUrl(string $ytVideoId): ?string
    {
        return Core::getConfig()->mediaBaseUrl . '/yt_thumbnail/' . $ytVideoId . '.jpg';
    }

    public static function buildYoutubeIFrameHtml(string $ytVideoId, bool $autoplay = false): ?string
    {
        if (!$ytVideoId) {
            return null;
        }

        $output = [
            'width="560"',
            'height="315"',
            'src="https://www.youtube-nocookie.com/embed/' . $ytVideoId . ($autoplay ? '?autoplay=1' : '') . '"',
            'title="Reprodutor de vídeo de YouTube"',
            'frameBorder="0"',
            'allow="encrypted-media; picture-in-picture"',
            'referrerpolicy="strict-origin-when-cross-origin"',
            'allowFullscreen="true"',
        ];

        return '<iframe ' . implode(' ', $output) . '></iframe>';
    }

    public static function storeYoutubeThumbnail(string $ytVideoId): bool
    {
        if (!is_dir(Core::getConfig()->mediaBaseDir . '/yt_thumbnail/')) {
            $resDir = mkdir(Core::getConfig()->mediaBaseDir . '/yt_thumbnail/', 0777, true);
            if (!$resDir) {
                return false;
            }
        }

        $count = 0;
        do {
            $filename = $count < 2 ? 'maxresdefault.jpg' : 'hqdefault.jpg';

            $res = copy(
                from: 'https://img.youtube.com/vi/' . $ytVideoId . '/' . $filename,
                to: Core::getConfig()->mediaBaseDir . '/yt_thumbnail/' . $ytVideoId . '.jpg',
            );
            $count++;
        } while (!$res && $count < 4);

        return $res;
    }
}
