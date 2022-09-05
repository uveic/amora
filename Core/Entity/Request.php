<?php

namespace Amora\Core\Entity;

use Amora\Core\Core;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\UserCore;
use Amora\App\Value\Language;

final class Request
{
    private ?array $parsedHeaders = null;

    public readonly ?Session $session;
    public readonly Language $siteLanguage;
    public readonly ?string $clientLanguage;

    public function __construct(
        public readonly ?string $sourceIp,
        public readonly ?string $userAgent,
        public readonly string $method, // The HTTP request verb (GET, POST, PUT, etc.)
        private string $path, // The request URI
        public readonly ?string $referrer,
        public readonly string $body,
        public readonly array $getParams,
        public readonly array $postParams,
        public readonly array $files,
        private readonly array $cookies,
        private readonly array $headers,
    ) {
        $this->session = $this->loadSession();
        $this->clientLanguage = $headers['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $this->siteLanguage = $this->calculateSiteLanguageAndUpdatePath();

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

    private function calculateSiteLanguageAndUpdatePath(): Language
    {
        $arrayPath = explode('/', $this->path);
        if (!empty($arrayPath[0]) && strlen($arrayPath[0]) == 2) {
            if (Language::tryFrom(strtoupper($arrayPath[0]))) {
                $siteLanguage = Language::from(strtoupper($arrayPath[0]));
                unset($arrayPath[0]);
                $this->path = implode('/', $arrayPath);
                return $siteLanguage;
            }
        }

        return $this->getSiteLanguageFromClientLanguage();
    }

    private function getSiteLanguageFromClientLanguage(): Language
    {
        if (count(Core::getAllLanguages()) == 1) {
            return Core::getDefaultLanguage();
        }

        if ($this->session) {
            return $this->session->user->language;
        }

        $parts = $this->clientLanguage ? explode(',', $this->clientLanguage) : [];

        foreach ($parts as $part) {
            $semicolon = strpos($part, ';');
            $lang = $semicolon !== false
                ? substr($part, 0, $semicolon)
                : $part;

            $dash = strpos($lang, '-');
            $lang = $dash !== false ? substr($lang, 0, $dash) : $lang;
            $lang = strtoupper($lang);

            if (Language::tryFrom($lang)) {
                return Language::from($lang);
            }
        }

        return Core::getDefaultLanguage();
    }

    private function loadSession(): ?Session {
        $sessionId = $this->getCookie('sid');
        return UserCore::getSessionService()->loadSession($sessionId);
    }
}