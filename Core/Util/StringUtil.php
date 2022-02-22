<?php

namespace Amora\Core\Util;

final class StringUtil
{
    public static function cleanString(
        string $text,
        string $charsToBeRemoved = '',
        string $replaceWith = '-'
    ): string
    {
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
        $text = preg_replace('/\s+/', $replaceWith, $text);
        $text = preg_replace('/' . $replaceWith . '+/', $replaceWith, $text);

        return trim($text, ' ' . $replaceWith);
    }

    public static function cleanPostCode(string $postCode): string
    {
        $postCode = strtoupper(trim($postCode));
        $postCode = trim(preg_replace('/[^A-Z0-9\s]+/', ' ', $postCode));
        $postCode = preg_replace('/\s+/', ' ', $postCode);
        return $postCode;
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
    public static function isTrue($value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return intval($value) === 1;
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

    public static function getRandomString(int $length = 24): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
        $text = str_replace('<br>', ' ', $text);
        $text = str_replace('<br/>', ' ', $text);
        return strip_tags($text);
    }

    public static function formatNumber(
        float $number,
        int $decimals = 0,
        string $language = 'EN',
        bool $includeThousandsSeparator = true,
    ): string {
        $language = strtoupper($language);

        if ($language === 'ES' || $language === 'GL') {
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

        $values = $commaSeparatedValues ? explode(',', $commaSeparatedValues) : [];
        return array_filter($values, function ($value) {
            return is_numeric($value);
        });
    }
}
