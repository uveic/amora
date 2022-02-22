<?php

namespace Amora\Core\Model\Response;

use Amora\App\Value\AppMenu;
use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Util\LocalisationUtil;

abstract class HtmlResponseDataAbstract
{
    public readonly LocalisationUtil $localisationUtil;
    public readonly string $baseUrl;
    public readonly string $baseUrlWithLanguage;
    public readonly string $siteUrl;
    public readonly string $sitePath;
    public readonly string $siteLanguageIsoCode;
    public readonly string $siteName;
    public readonly int $lastUpdatedTimestamp;
    public readonly string $pageTitleWithoutSiteName;

    public function __construct(
        public readonly Request $request,
        public readonly ?Pagination $pagination = null,
        protected ?string $pageTitle = null,
        protected ?string $pageDescription = null,
        protected ?string $siteImageUri = null,
    ) {
        $this->localisationUtil = Core::getLocalisationUtil(strtoupper($request->siteLanguageIsoCode));

        $baseUrl = Core::getConfigValue('baseUrl');
        $siteImageUrl = Core::getConfigValue('siteImageUrl');

        $this->baseUrl = empty($baseUrl) ? '' : $baseUrl;
        $this->sitePath = !$request->getPath() || $request->getPath() === 'home'
            ? '/'
            : $request->getPath();
        $this->siteUrl = trim($this->baseUrl, ' /') . '/' . ltrim($this->sitePath, ' /');
        $this->siteLanguageIsoCode = $request->siteLanguageIsoCode;
        $this->siteName = $this->localisationUtil->getValue('siteName');
        $this->siteImageUri = $siteImageUri ?? ($siteImageUrl ?? '');
        $this->lastUpdatedTimestamp = time();

        $this->pageTitle = isset($pageTitle) && $pageTitle
            ? $pageTitle . ' - ' . $this->siteName
            : $this->getSiteNameAndTitle();
        $this->pageTitleWithoutSiteName = $pageTitle ?? '';
        $this->pageDescription = $pageDescription
            ?? $this->localisationUtil->getValue('siteDescription');
        $this->baseUrlWithLanguage = $this->baseUrl .
            strtolower($this->siteLanguageIsoCode) . '/';
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle ?: $this->siteName;
    }

    public function getPageDescription(): string
    {
        return $this->pageDescription;
    }

    public function getSiteLogoUrl(): ?string {
        return Core::getConfigValue('logoUrl');
    }

    private function getSiteNameAndTitle(): string
    {
        $siteTitle = $this->localisationUtil->getValue('siteTitle');
        return $this->siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    public function getSiteImageUri(): string
    {
        return $this->siteImageUri;
    }

    public function getSiteDomain(): string
    {
        return parse_url($this->baseUrl, PHP_URL_HOST);
    }

    public function getLocalValue(string $key): string
    {
        return $this->localisationUtil->getValue($key);
    }

    public function getMenu(bool $forceCustomerMenu = false): array
    {
        if ($this->request->session && $this->request->session->isAdmin() && !$forceCustomerMenu) {
            return AppMenu::getAdminAll(
                languageIsoCode: $this->siteLanguageIsoCode,
                username: $this->request->session->user->getNameOrEmail(),
            );
        }

        if ($this->request->session) {
            return AppMenu::getCustomerAll(
                languageIsoCode: $this->siteLanguageIsoCode,
                username: $this->request->session->user->getNameOrEmail(),
                includeAdminLink: $this->request->session->isAdmin(),
            );
        }

        return [];
    }

    public function isUserVerified(): bool
    {
        if (empty($this->request->session)) {
            return false;
        }
        return $this->request->session->user->verified;
    }

    public function minutesSinceUserRegistration(): int
    {
        if (empty($this->request->session)) {
            return 0;
        }

        return round((time() - $this->request->session->user->createdAt->getTimestamp()) / 60);
    }
}
