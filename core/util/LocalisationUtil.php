<?php

namespace uve\core\util;

use uve\core\Core;
use uve\core\Logger;
use uve\core\value\Language;

final class LocalisationUtil
{
    private array $values;

    public function __construct(
        private Logger $logger,
        private string $defaultLocalisationIsoCode,
        private string $languageIsoCode
    ) {
        $this->defaultLocalisationIsoCode = strtoupper($this->defaultLocalisationIsoCode);
        $this->languageIsoCode = strtoupper($this->languageIsoCode);
        $availableLanguagesIsoCodes = array_column(Language::getAvailableLanguages(), 'iso_code');

        if (!in_array($this->languageIsoCode, $availableLanguagesIsoCodes)) {
            $this->logger->logError('Localisation language not found: ' . $this->languageIsoCode);
            $this->languageIsoCode = $this->defaultLocalisationIsoCode;
        }

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
        $filePath = Core::getPathRoot() . '/core/value/localisation/' . $languageIsoCode . '.php';

        if (!file_exists($filePath)) {
            $this->logger->logError('Localisation file not found: ' . $filePath);
            return [];
        }

        return require $filePath;
    }
}
