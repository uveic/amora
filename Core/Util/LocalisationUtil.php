<?php

namespace Amora\Core\Util;

use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Value\Language;

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
        $coreFilePath = Core::getPathRoot() . '/Core/Value/Localisation/' . $languageIsoCode . '.php';

        if (!file_exists($coreFilePath)) {
            $this->logger->logError('Localisation file not found: ' . $coreFilePath);
            $coreValues = [];
        } else {
            $coreValues = require $coreFilePath;
        }

        $appFilePath = Core::getPathRoot() . '/App/Value/Localisation/' . $languageIsoCode . '.php';

        if (!file_exists($appFilePath)) {
            $this->logger->logError('Localisation file not found: ' . $appFilePath);
            $appValues = [];
        } else {
            $appValues = require $appFilePath;
        }

        return array_merge($coreValues, $appValues);
    }
}
