<?php

namespace Amora\Core\Value;

final class Language
{
    const ENGLISH = 1;
    const GALEGO = 2;
    const ESPANOL = 3;

    public static function getAll(): array
    {
        return [
            self::ENGLISH => [
                'id' => self::ENGLISH,
                'name' => 'English',
                'iso_code' => 'EN',
                'is_enabled' => true
            ],
            self::GALEGO => [
                'id' => self::GALEGO,
                'name' => 'Galego',
                'iso_code' => 'GL',
                'is_enabled' => true
            ],
            self::ESPANOL => [
                'id' => self::ESPANOL,
                'name' => 'Español',
                'iso_code' => 'ES',
                'is_enabled' => true
            ],
        ];
    }

    public static function getNameForId(int $languageId): string
    {
        $all = self::getAll();

        return empty($all[$languageId])
            ? 'Unknown'
            : $all[$languageId]['name'];
    }

    public static function getIsoCodeForId(int $languageId): string
    {
        $all = self::getAll();

        return empty($all[$languageId])
            ? 'Unknown'
            : $all[$languageId]['iso_code'];
    }

    public static function getIdForIsoCode(string $isoCode): int
    {
        $all = self::getAll();
        $isoCode = strtoupper($isoCode);

        foreach ($all as $language) {
            if ($language['iso_code'] == $isoCode) {
                return $language['id'];
            }
        }

        return self::ENGLISH;
    }

    public static function getAvailableLanguages(): array
    {
        $all = self::getAll();
        $output = [];

        foreach ($all as $language) {
            if (empty($language['is_enabled'])) {
                continue;
            }

            $output[] = $language;
        }

        return $output;
    }

    public static function getAvailableIsoCodes(): array
    {
        return array_column(self::getAvailableLanguages(), 'iso_code');
    }

    public static function isValidIsoCode(string $languageIsoCode): bool
    {
        return in_array(strtoupper($languageIsoCode), self::getAvailableIsoCodes());
    }
}
