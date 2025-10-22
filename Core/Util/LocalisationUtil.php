<?php

namespace Amora\Core\Util;

use Amora\Core\Core;
use Amora\App\Value\Language;

final class LocalisationUtil
{
    private array $values;

    public function __construct(
        private readonly Logger $logger,
        public readonly Language $language,
    ) {
        $this->values = $this->loadValues($this->language);
    }

    public function getValue(string $key): string
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        $this->logger->logError(
            'Localisation not set for key: ' . $key .
            ' - Language: ' . $this->language->value
        );

        $defaultValues = $this->loadValues($this->language);
        if (isset($defaultValues[$key])) {
            return $defaultValues[$key];
        }

        $this->logger->logError(
            'Localisation not set for key: ' . $key .
            ' - Language: ' . $this->language->value
        );

        return '';
    }

    private function loadValues(Language $language): array {
        $coreFilePath = Core::getPathRoot() . '/Core/Value/Localisation/' . $language->value . '.php';
        if (!file_exists($coreFilePath)) {
            $coreFilePath = Core::getPathRoot() . '/Core/Value/Localisation/' . Core::getDefaultLanguage()->value . '.php';
            if (!file_exists($coreFilePath)) {
                $this->logger->logError('Localisation file not found: ' . $coreFilePath);
                $coreFilePath = null;
            }
        }

        $appFilePath = Core::getPathRoot() . '/App/Value/Localisation/' . $language->value . '.php';
        if (!file_exists($appFilePath) && !file_exists($coreFilePath)) {
            $this->logger->logError('Localisation file not found: ' . $appFilePath);
            $appFilePath = null;
        }

        $coreValues = $coreFilePath ? require $coreFilePath : [];
        $appValues = $appFilePath ? require $appFilePath : [];

        return array_merge($coreValues, $appValues);
    }
}
