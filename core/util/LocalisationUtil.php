<?php

namespace uve\core\util;

use uve\core\Core;
use uve\core\Logger;
use uve\value\Language;

final class LocalisationUtil
{
    private Logger $logger;
    private string $defaultLocalisationIsoCode;
    private string $languageIsoCode;
    private array $values;

    public function __construct(
        Logger $logger,
        string $defaultSiteLanguage,
        string $languageIsoCode
    ) {
        $this->logger = $logger;
        $this->defaultLocalisationIsoCode = $defaultSiteLanguage;
        $languageIsoCode = strtoupper($languageIsoCode);
        $availableLanguagesIsoCodes = array_column(Language::getAvailableLanguages(), 'iso_code');

        if (!in_array($languageIsoCode, $availableLanguagesIsoCodes)) {
            $this->logger->logError('Localisation language not found: ' . $languageIsoCode);
            $languageIsoCode = $this->defaultLocalisationIsoCode;
        }

        $this->languageIsoCode = $languageIsoCode;
        $this->values = $this->loadValues($this->languageIsoCode);
    }

    public function getValue(string $key): string
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        $this->logger->logError(
            'Localisation not set for key: ' . $key .
            ' - Language: ' . $this->languageIsoCode
        );

        $defaultValues = $this->loadValues($this->defaultLocalisationIsoCode);
        if (isset($defaultValues[$key])) {
            return $defaultValues[$key];
        }

        $this->logger->logError(
            'Localisation not set for key in site default language: ' . $key .
            ' - Site default language: ' . $this->defaultLocalisationIsoCode
        );

        return '';
    }

    private function loadValues(string $languageIsoCode): array {
        $filePath = Core::getPathRoot() . '/value/localisation/' . $languageIsoCode . '.php';

        if (!file_exists($filePath)) {
            $this->logger->logError('Localisation file not found: ' . $filePath);
            return [];
        }

        return require $filePath;
    }
}
