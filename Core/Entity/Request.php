<?php

namespace Amora\Core\Entity;

use Amora\Core\Core;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\UserCore;
use Amora\App\Value\Language;
use JsonException;

final readonly class Request
{
    public ?Session $session;
    public Language $siteLanguage;
    public ?string $clientLanguage;
    public array $pathWithoutLanguage;

    public function __construct(
        public ?string $sourceIp,
        public ?string $userAgent,
        public string $method, // The HTTP request verb (GET, POST, PUT, etc.)
        public string $path, // The request URI
        public ?string $referrer,
        public string $body,
        public array $getParams,
        public array $postParams,
        public array $files,
        private array $cookies,
        array $headers,
    ) {
        $this->session = $this->loadSession();
        $this->clientLanguage = $headers['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $this->pathWithoutLanguage = $this->getArrayPathWithoutLanguage();
        $this->siteLanguage = $this->calculateSiteLanguage();
    }

    ////////////////////////////////////////////////////////////////////////////
    // Getters / Setters

    public function getAction(): string
    {
        return $this->pathWithoutLanguage[0] ?? 'home';
    }

    public function getPathWithoutLanguageAsString(): string
    {
        return implode('/', $this->pathWithoutLanguage);
    }

    public function getBodyPayload(): array
    {
        try {
            return empty($this->body) ? [] : json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Core::getDefaultLogger()->logException($e);
            return [];
        }
    }

    public function getGetParam(string $paramName): ?string
    {
        return $this->getParams[$paramName] ?? null;
    }

    public function getCookie(string $cookieName): ?string
    {
        return $this->cookies[$cookieName] ?? null;
    }

    private function getArrayPathWithoutLanguage(): array
    {
        if (empty($this->path)) {
            return ['home'];
        }

        $arrayPath = explode('/', $this->path);
        if (!empty($arrayPath[0]) && strlen($arrayPath[0]) === 2) {
            $enabledLanguages = Core::getEnabledSiteLanguages();
            $uppercaseLanguage = strtoupper($arrayPath[0]);

            if (Language::tryFrom($uppercaseLanguage)) {
                $language = Language::from($uppercaseLanguage);

                if (in_array($language, $enabledLanguages, true)) {
                    unset($arrayPath[0]);
                }
            }
        }

        return empty($arrayPath)
            ? ['home']
            : array_values($arrayPath);
    }

    private function calculateSiteLanguage(): Language
    {
        $enabledLanguages = Core::getEnabledSiteLanguages();

        $arrayPath = explode('/', $this->path);
        if (!empty($arrayPath[0]) && strlen($arrayPath[0]) === 2) {
            $uppercaseLanguage = strtoupper($arrayPath[0]);
            if (Language::tryFrom($uppercaseLanguage)) {
                $language = Language::from($uppercaseLanguage);

                if (in_array($language, $enabledLanguages, true)) {
                    return $language;
                }
            }
        }

        if (count($enabledLanguages) === 1) {
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
                $language = Language::from($lang);
                if (in_array($language, $enabledLanguages, true)) {
                    return $language;
                }
            }
        }

        return Core::getDefaultLanguage();
    }

    private function loadSession(): ?Session
    {
        $sessionId = $this->getCookie(Core::getConfig()->sessionIdCookieName);
        return UserCore::getSessionService()->loadSession($sessionId);
    }
}
