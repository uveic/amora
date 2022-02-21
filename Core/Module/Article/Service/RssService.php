<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Util\LocalisationUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;
use DateTimeZone;

class RssService
{
    protected LocalisationUtil $localisationUtil;

    public function __construct(
        private Logger $logger,
    ) {}

    public function buildRss(
        string $siteLanguage,
        array $articles,
    ): string {
        $this->localisationUtil = Core::getLocalisationUtil(strtoupper($siteLanguage));

        $this->logger->logInfo('Building RSS...');

        $lastBuildDate = $this->getBuildDate($articles);

        $xml = array_merge(
            [
                '<?xml version="1.0"?>',
                '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
                '<channel>',
            ],
            $this->buildHeader(
                siteLanguage: $siteLanguage,
                lastPubDate: $this->getLastPubDate($articles[0] ?? null),
                lastBuildDate: $lastBuildDate,
            ),
            $this->buildContent(
                siteLanguage: $siteLanguage,
                articles: $articles,
            ),
            [
                '</channel>',
                '</rss>',
            ]
        );

        return implode('', $xml);
    }

    private function buildHeader(
        string $siteLanguage,
        DateTimeImmutable $lastPubDate,
        DateTimeImmutable $lastBuildDate,
    ): array {
        $baseUrl = Core::getConfigValue('baseUrl');
        $siteAdminEmail = Core::getConfigValue('siteAdminEmail');
        $siteAdminName = Core::getConfigValue('siteAdminName');
        $siteTitle = $this->getSiteTitle();
        $siteDescription = $this->localisationUtil->getValue('siteDescription');

        $output = [
            '<title>' . $siteTitle . '</title>',
            '<link>' . $baseUrl . '</link>',
            '<description>' . $siteDescription . '</description>',
            '<language>' . strtolower($siteLanguage) . '</language>',
            '<pubDate>' . $lastPubDate->format('r') . '</pubDate>',
            '<lastBuildDate>' . $lastBuildDate->format('r') . '</lastBuildDate>',
            '<docs>http://blogs.law.harvard.edu/tech/rss</docs>',
            '<generator>' . $siteTitle . '</generator>',
            '<atom:link href="' . UrlBuilderUtil::buildPublicRssUrl() . '" rel="self" type="application/rss+xml" />',
        ];

        if ($siteAdminEmail && $siteAdminName) {
            $output[] = '<managingEditor>' . $siteAdminEmail . ' (' . $siteAdminName . ')</managingEditor>';
            $output[] = '<webMaster>' . $siteAdminEmail . ' (' . $siteAdminName . ')</webMaster>';
        }

        return $output;
    }

    private function buildContent(string $siteLanguage, array $articles): array
    {
        $output = [];

        /** @var Article $article */
        foreach ($articles as $article) {
            $link = UrlBuilderUtil::buildPublicArticleUrl(
                uri: $article->uri,
                languageIsoCode: $siteLanguage,
            );
            $title = $article->title ? htmlspecialchars($article->title) : '';
            $content = $this->getContent($article);

            $output[] = '<item>';
            $output[] = '<title>' . $title . '</title>';
            $output[] = '<link>' . $link . '</link>';
            $output[] = '<guid>' . $link . '</guid>';
            $output[] = '<author>' . $article->user->email . ' (' . $article->user->name . ')</author>';
            $output[] = '<description>' . $content . '</description>';
            $output[] = '<pubDate>' . $article->publishOn->format('r') . '</pubDate>';

            /** @var Tag $tag */
            foreach ($article->tags as $tag) {
                $output[] = '<category>' . $tag->name . '</category>';
            }

            $output[] = '</item>';
        }

        return $output;
    }

    private function getContent(Article $article): string
    {
        return htmlspecialchars(
            str_replace(
                search: 'src="/',
                replace: 'src="' . UrlBuilderUtil::buildBaseUrlWithoutLanguage() . '/',
                subject: $article->contentHtml,
            )
        );
    }

    private function getSiteTitle(): string
    {
        $siteTitle = $this->localisationUtil->getValue('siteTitle');
        $siteName = $this->localisationUtil->getValue('siteName');

        return $siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    private function getLastPubDate(?Article $article): DateTimeImmutable
    {
        $utcTimezone = new DateTimeZone('UTC');

        if (!$article) {
            return new DateTimeImmutable('now', $utcTimezone);
        }

        return new DateTimeImmutable($article->publishOn, $utcTimezone);
    }

    private function getBuildDate(array $articles): DateTimeImmutable
    {
        if (!$articles) {
            return new DateTimeImmutable();
        }

        $buildDate = $this->getLastPubDate($articles[0]);

        /** @var Article $article */
        foreach ($articles as $article) {
            $updatedAt = $article->publishOn;
            if ($buildDate < $updatedAt) {
                $buildDate = $updatedAt;
            }
        }

        return $buildDate->setTimezone(new DateTimeZone('UTC'));
    }
}
