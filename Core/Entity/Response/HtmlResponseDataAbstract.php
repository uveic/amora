<?php

namespace Amora\Core\Entity\Response;

use Amora\Core\Core;
use Amora\Core\Entity\Request;
use Amora\Core\Util\LocalisationUtil;
use Amora\App\Value\Language;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

abstract class HtmlResponseDataAbstract
{
    public readonly LocalisationUtil $localisationUtil;
    public readonly string $baseUrl;
    public readonly string $siteUrl;
    public readonly string $sitePath;
    public readonly Language $siteLanguage;
    public readonly string $siteName;
    public ?string $siteImagePath;
    public readonly int $lastUpdatedTimestamp;
    public readonly string $pageTitleWithoutSiteName;
    public readonly string $nonce;

    public function __construct(
        public readonly Request $request,
        public readonly ?Pagination $pagination = null,
        protected ?string $pageTitle = null,
        protected ?string $pageDescription = null,
        ?string $siteImagePath = null,
        ?int $lastUpdatedTimestamp = null,
    ) {
        $this->localisationUtil = Core::getLocalisationUtil($request->siteLanguage);

        $baseUrl = Core::getConfig()->baseUrl;
        $siteImageUrl = Core::getConfig()->siteImageUrl;

        $this->baseUrl = empty($baseUrl) ? '' : $baseUrl;
        $this->sitePath = $this->request->path ?: '/';
        $this->siteUrl = trim($this->baseUrl, ' /') . '/' . ltrim($this->sitePath, ' /');
        $this->siteLanguage = $request->siteLanguage;
        $this->siteName = $this->localisationUtil->getValue('siteName');

        $imagePath = parse_url($siteImagePath ?? ($siteImageUrl ?? ''), PHP_URL_PATH);
        $this->siteImagePath = UrlBuilderUtil::buildBaseUrlWithoutLanguage() . $imagePath;
        $this->lastUpdatedTimestamp = $lastUpdatedTimestamp ?? time();

        $this->pageTitle = isset($pageTitle) && $pageTitle
            ? $pageTitle . ' - ' . $this->siteName
            : $this->getSiteNameAndTitle();
        $this->pageTitleWithoutSiteName = $pageTitle ?? '';
        $this->pageDescription = $pageDescription
            ?? $this->localisationUtil->getValue('siteDescription');

        $this->nonce = StringUtil::generateNonce();
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
        return Core::getConfig()->logoImageUrl;
    }

    private function getSiteNameAndTitle(): string
    {
        $siteTitle = $this->localisationUtil->getValue('siteTitle');
        return $this->siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    public function getSiteDomain(): string
    {
        return parse_url($this->baseUrl, PHP_URL_HOST);
    }

    public function getLocalValue(string $key): string
    {
        return $this->localisationUtil->getValue($key);
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
