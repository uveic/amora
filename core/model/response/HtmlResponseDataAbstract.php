<?php

namespace uve\core\model\response;

use uve\App\Value\AppMenu;
use uve\core\Core;
use uve\core\model\Request;
use uve\core\util\LocalisationUtil;
use uve\Core\Value\CoreMenu;

abstract class HtmlResponseDataAbstract
{
    protected LocalisationUtil $localisationUtil;
    protected string $baseUrl;
    protected string $baseUrlWithLanguage;
    protected string $siteUrl;
    protected string $sitePath;
    protected string $siteLanguage;
    protected string $siteName;
    protected string $siteImageUri;
    protected int $lastUpdatedTimestamp;

    protected string $pageTitle;
    protected string $pageTitleWithoutSiteName;
    protected string $pageDescription;

    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $siteImageUri = null
    ) {
        $this->localisationUtil = Core::getLocalisationUtil(strtoupper($request->getSiteLanguage()));

        $baseUrl = Core::getConfigValue('baseUrl');
        $siteImageUrl = Core::getConfigValue('siteImageUrl');

        $sitePath = !$request->getPath() || $request->getPath() === 'home'
            ? '/'
            : $request->getPath();

        $this->baseUrl = empty($baseUrl) ? '' : $baseUrl;
        $this->sitePath = $sitePath;
        $this->siteUrl = trim($this->baseUrl, ' /') . '/' . ltrim($this->sitePath, ' /');
        $this->siteLanguage = $request->getSiteLanguage();
        $this->siteName = $this->localisationUtil->getValue('siteName');
        $this->siteImageUri = $siteImageUri ?? ($siteImageUrl ?? '');
        $this->lastUpdatedTimestamp = time();

        $this->pageTitle = isset($pageTitle) && $pageTitle
            ? $pageTitle . ' - ' . $this->getSiteName()
            : $this->getSiteName();
        $this->pageTitleWithoutSiteName = $this->pageTitle ?? '';
        $this->pageDescription = $pageDescription
            ?? $this->localisationUtil->getValue('siteDescription');
        $this->baseUrlWithLanguage = $this->getBaseUrl() .
            strtolower($this->getSiteLanguage()) . '/';
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getBaseUrlWithLanguage(): string
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

    public function getSiteDomain(): string
    {
        return parse_url($this->getBaseUrl(), PHP_URL_HOST);
    }

    public function getLocalValue(string $key): string
    {
        return $this->localisationUtil->getValue($key);
    }

    public function getTimezone(): string
    {
        if (!$this->getSession()) {
            return Core::getDefaultTimezone();
        }

        return $this->getSession()->getTimezone();
    }

    public function getMenu(): array
    {
        if ($this->getSession() && $this->getSession()->isAdmin()) {
            return array_merge(
                CoreMenu::getAdminMenu($this->getBaseUrlWithLanguage(), $this->getSiteLanguage()),
                AppMenu::getAdminAll($this->getBaseUrlWithLanguage(), $this->getSiteLanguage())
            );
        }

        if ($this->getSession()) {
            return AppMenu::getCustomerAll(
                $this->getBaseUrlWithLanguage(),
                $this->getSiteLanguage()
            );
        }

        return [];
    }
}
