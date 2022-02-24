<?php

namespace Amora\Core\Model;

use Amora\Core\Core;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\UserCore;
use Amora\Core\Value\Language;

final class Request
{
    private ?array $parsedHeaders = null;

    public readonly ?Session $session;
    public readonly string $siteLanguageIsoCode;
    public readonly ?string $clientLanguage;
    public readonly array $processedFiles;

    public function __construct(
        public readonly ?string $sourceIp,
        public readonly ?string $userAgent,
        public readonly string $method, // The HTTP request verb (GET, POST, PUT, etc.)
        private string $path, // The request URI
        public readonly ?string $referrer,
        public readonly string $body,
        public readonly array $getParams,
        public readonly array $postParams,
        array $files,
        private array $cookies,
        private array $headers,
    ) {
        $this->processedFiles = $this->processFiles($files);
        $this->session = $this->loadSession();
        $this->clientLanguage = $headers['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $this->siteLanguageIsoCode = $this->calculateSiteLanguageAndUpdatePath();

        if (empty($this->path)) {
            $this->path = 'home';
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Getters / Setters

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParsedHeaders(): array
    {
        if (isset($this->parsedHeaders)) {
            return $this->parsedHeaders;
        }

        $headers = array();
        foreach($this->headers as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $header = str_replace(
                ' ',
                '-',
                ucwords(str_replace('_', ' ', strtolower(substr($key, 5))))
            );
            $headers[$header] = $value;
        }

        $this->parsedHeaders = $headers;
        return $headers;
    }

    public function getBodyPayload(): array
    {
        return empty($this->body) ? [] : json_decode($this->body, true);
    }

    public function getGetParam(string $paramName): ?string
    {
        $getParams = $this->getParams;
        return $getParams[$paramName] ?? null;
    }

    public function getCookie(string $cookieName): ?string
    {
        $cookies = $this->cookies;
        return empty($cookies[$cookieName]) ? null : $cookies[$cookieName];
    }

    public function getBodyParam(string $paramName)
    {
        $bodyParams = $this->getBodyPayload();
        return $bodyParams[$paramName] ?? null;
    }

    private function processFiles(array $files): array
    {
        $output = [];

        if (empty($files['files']['name'][0])) {
            return $output;
        }

        $key = 0;
        foreach ($files['files']['name'] as $ignored) {
            $output[] = new File(
                $files['files']['name'][$key],
                $files['files']['tmp_name'][$key],
                $files['files']['size'][$key],
                $files['files']['type'][$key],
                $files['files']['error'][$key]
            );

            $key++;
        }

        return $output;
    }

    private function calculateSiteLanguageAndUpdatePath(): string
    {
        $arrayPath = explode('/', $this->path);
        if (!empty($arrayPath[0]) && strlen($arrayPath[0]) == 2) {
            if (in_array(strtoupper($arrayPath[0]), Language::getAvailableIsoCodes())) {
                $siteLanguage = strtoupper($arrayPath[0]);
                unset($arrayPath[0]);
                $this->path = implode('/', $arrayPath);
                return $siteLanguage;
            }
        }

        return $this->getSiteLanguageFromClientLanguage();
    }

    public function getSiteLanguageFromClientLanguage(): string
    {
        if ($this->session) {
            return Language::getIsoCodeForId($this->session->user->languageId);
        }

        $parts = $this->clientLanguage ? explode(',', $this->clientLanguage) : [];
        $availableLanguageIsoCodes = array_column(Language::getAvailableLanguages(), 'iso_code');

        foreach ($parts as $part) {
            $semicolon = strpos($part, ';');
            $lang = $semicolon !== false
                ? substr($part, 0, $semicolon)
                : $part;

            $dash = strpos($lang, '-');
            $lang = $dash !== false ? substr($lang, 0, $dash) : $lang;
            $lang = strtoupper($lang);

            if (in_array($lang, $availableLanguageIsoCodes)) {
                return $lang;
            }
        }

        return strtoupper(Core::getConfig()->defaultSiteLanguageIsoCode);
    }

    private function loadSession(): ?Session {
        $sessionId = $this->getCookie('sid');
        return UserCore::getSessionService()->loadSession($sessionId);
    }
}
