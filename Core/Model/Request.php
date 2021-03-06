<?php

namespace Amora\Core\Model;

use Amora\Core\Core;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\UserCore;
use Amora\Core\Value\Language;

final class Request
{
    private ?string $sessionId;
    private ?Session $session;
    private string $siteLanguage;
    private ?string $clientLanguage;

    public function __construct(
        private ?string $sourceIp,
        private ?string $userAgent,
        private string $verb, // The HTTP request verb (GET, POST, PUT, etc.)
        private string $path, // The request URI
        private ?string $referrer,
        private string $body,
        private array $getParams,
        private array $postParams,
        private array $files,
        private array $cookies,
        private array $headers
    ) {
        $this->verb = strtoupper($verb);
        $this->files = $this->processFiles($files);
        $this->sessionId = $this->getCookie('sid');
        $this->session = $this->loadSession();
        $this->clientLanguage = $headers['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $this->siteLanguage = $this->calculateSiteLanguage();

        if (empty($this->path)) {
            $this->path = 'home';
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Getters / Setters

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function getSourceIp(): ?string
    {
        return empty($this->sourceIp) ? null : $this->sourceIp;
    }

    public function getUserAgent(): ?string
    {
        return empty($this->userAgent) ? null : $this->userAgent;
    }

    public function getMethod(): string
    {
        return $this->verb;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function getSiteLanguage(): string
    {
        return $this->siteLanguage;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getParsedHeaders(): array
    {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }

            $header = str_replace(
                ' ',
                '-',
                ucwords(str_replace('_', ' ', strtolower(substr($key, 5))))
            );
            $headers[$header] = $value;
        }

        return $headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getBodyPayload(): array
    {
        return empty($this->getBody()) ? [] : json_decode($this->getBody(), true);
    }

    public function getGetParams(): array
    {
        return $this->getParams;
    }

    public function getGetParam(string $paramName): ?string
    {
        $getParams = $this->getGetParams();
        return $getParams[$paramName] ?? null;
    }

    public function getPostParams(): array
    {
        return $this->postParams;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getClientLanguage(): ?string
    {
        return $this->clientLanguage;
    }

    public function getCookie(string $cookieName): ?string
    {
        $cookies = $this->getCookies();
        return empty($cookies[$cookieName]) ? null : $cookies[$cookieName];
    }

    public function getBodyParam(string $paramName)
    {
        $bodyParams = $this->getBodyPayload();
        return isset($bodyParams[$paramName]) ? $bodyParams[$paramName] : null;
    }

    private function processFiles(array $files): array
    {
        $output = [];

        if (empty($files['files']['name'][0])) {
            return $output;
        }

        $key = 0;
        foreach ($files['files']['name'] as $file) {
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

    private function calculateSiteLanguage(): string
    {
        $arrayPath = explode('/', $this->getPath());
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
        if ($this->getSession()) {
            return Language::getIsoCodeForId($this->getSession()->getUser()->getLanguageId());
        }

        $parts = explode(',', $this->clientLanguage);
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

        return strtoupper(Core::getConfigValue('defaultSiteLanguage'));
    }

    private function loadSession(): ?Session {
        return UserCore::getSessionService()->loadSession($this->getSessionId());
    }
}
