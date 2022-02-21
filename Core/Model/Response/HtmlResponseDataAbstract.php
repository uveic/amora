<?php

namespace Amora\Core\Model\Response;

use Amora\App\Value\AppMenu;
use Amora\Core\Core;
use Amora\Core\Model\Request;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Util\LocalisationUtil;

abstract class HtmlResponseDataAbstract
{
    protected LocalisationUtil $localisationUtil;
    protected ?Session $session;
    protected string $baseUrl;
    protected string $baseUrlWithLanguage;
    protected string $siteUrl;
    protected string $sitePath;
    protected string $siteLanguage;
    protected string $siteName;
    protected int $lastUpdatedTimestamp;
    protected string $pageTitleWithoutSiteName;

    public function __construct(
        private Request $request,
        protected ?string $pageTitle = null,
        protected ?string $pageDescription = null,
        protected ?string $siteImageUri = null,
        protected ?Pagination $pagination = null,
    ) {
        $this->session = $request->session;
        $this->localisationUtil = Core::getLocalisationUtil(strtoupper($request->siteLanguageIsoCode));

        $baseUrl = Core::getConfigValue('baseUrl');
        $siteImageUrl = Core::getConfigValue('siteImageUrl');

        $sitePath = !$request->getPath() || $request->getPath() === 'home'
            ? '/'
            : $request->getPath();

        $this->baseUrl = empty($baseUrl) ? '' : $baseUrl;
        $this->sitePath = $sitePath;
        $this->siteUrl = trim($this->baseUrl, ' /') . '/' . ltrim($this->sitePath, ' /');
        $this->siteLanguage = $request->siteLanguageIsoCode;
        $this->siteName = $this->localisationUtil->getValue('siteName');
        $this->siteImageUri = $siteImageUri ?? ($siteImageUrl ?? '');
        $this->lastUpdatedTimestamp = time();

        $this->pageTitle = isset($pageTitle) && $pageTitle
            ? $pageTitle . ' - ' . $this->getSiteName()
            : $this->getSiteNameAndTitle();
        $this->pageTitleWithoutSiteName = $pageTitle ?? '';
        $this->pageDescription = $pageDescription
            ?? $this->localisationUtil->getValue('siteDescription');
        $this->baseUrlWithLanguage = $this->buildBaseUrl() .
            strtolower($this->getSiteLanguage()) . '/';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function buildBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function buildBaseUrlWithLanguage(): string
    {
        return $this->baseUrlWithLanguage;
    }

    public function getSiteLanguage(): string
    {
        return $this->siteLanguage;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle ? $this->pageTitle : $this->siteName;
    }

    public function getPageTitleWithoutSiteName(): string
    {
        return $this->pageTitleWithoutSiteName;
    }

    public function getPageDescription(): string
    {
        return $this->pageDescription;
    }

    public function getSiteName(): string
    {
        return $this->siteName;
    }

    public function getSiteLogoUrl(): ?string {
        return Core::getConfigValue('logoUrl');
    }

    public function getSiteNameAndTitle(): string
    {
        $siteTitle = $this->localisationUtil->getValue('siteTitle');
        return $this->getSiteName() . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    public function getSiteImageUri(): string
    {
        return $this->siteImageUri;
    }

    public function getLasUpdatedTimestamp(): int
    {
        return $this->lastUpdatedTimestamp;
    }

    public function getSitePath(): string
    {
        return $this->sitePath;
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }

    public function getSiteDomain(): string
    {
        return parse_url($this->buildBaseUrl(), PHP_URL_HOST);
    }

    public function getLocalValue(string $key): string
    {
        return $this->localisationUtil->getValue($key);
    }

    public function getTimezone(): string
    {
        if (!$this->session) {
            return Core::getDefaultTimezone();
        }

        return $this->session->timezone->getName();
    }

    public function getMenu(bool $forceCustomerMenu = false): array
    {
        if ($this->session && $this->session->isAdmin() && !$forceCustomerMenu) {
            return AppMenu::getAdminAll(
                languageIsoCode: $this->getSiteLanguage(),
                username: $this->session->user->getNameOrEmail(),
            );
        }

        if ($this->session) {
            return AppMenu::getCustomerAll(
                languageIsoCode: $this->getSiteLanguage(),
                username: $this->session->user->getNameOrEmail(),
                includeAdminLink: $this->session->isAdmin(),
            );
        }

        return [];
    }

    public function isUserVerified(): bool
    {
        if (empty($this->session)) {
            return false;
        }
        return $this->session->user->verified;
    }

    public function minutesSinceUserRegistration(): int
    {
        if (empty($this->session)) {
            return 0;
        }

        return round((time() - $this->session->user->createdAt->getTimestamp()) / 60);
    }
}
