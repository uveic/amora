<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Util\LocalisationUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;
use DateTimeZone;

class XmlService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function buildSitemap(array $articles): string
    {
        $this->logger->logInfo('Building sitemap...');

        $xml = array_merge(
            [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">',
            ],
            $this->buildSitemapContent(
                articles: $articles,
            ),
            [
                '</urlset>',
            ]
        );

        $this->logger->logInfo('Building sitemap done.');

        return implode('', $xml);
    }

    public function buildRss(
        LocalisationUtil $localisationUtil,
        array $articles,
    ): string {
        $this->logger->logInfo('Building RSS...');

        $lastBuildDate = $this->getBuildDate($articles);

        $xml = array_merge(
            [
                '<?xml version="1.0"?>',
                '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
                '<channel>',
            ],
            $this->buildRssHeader(
                localisationUtil: $localisationUtil,
                lastPubDate: $this->getLastPubDate($articles[0] ?? null),
                lastBuildDate: $lastBuildDate,
            ),
            $this->buildRssContent(
                siteLanguage: $localisationUtil->language,
                articles: $articles,
            ),
            [
                '</channel>',
                '</rss>',
            ]
        );

        $this->logger->logInfo('Building RSS done.');

        return implode('', $xml);
    }

    private function buildRssHeader(
        LocalisationUtil $localisationUtil,
        DateTimeImmutable $lastPubDate,
        DateTimeImmutable $lastBuildDate,
    ): array {
        $baseUrl = Core::getConfig()->baseUrl;
        $siteAdminEmail = Core::getConfig()->siteAdminEmail;
        $siteAdminName = Core::getConfig()->siteAdminName;
        $siteTitle = $this->getSiteTitle(
            siteTitle: $localisationUtil->getValue('siteTitle'),
            siteName: $localisationUtil->getValue('siteName'),
        );
        $siteDescription = $localisationUtil->getValue('siteDescription');

        $output = [
            '<title>' . $siteTitle . '</title>',
            '<link>' . $baseUrl . '</link>',
            '<description>' . $siteDescription . '</description>',
            '<language>' . strtolower($localisationUtil->language->value) . '</language>',
            '<pubDate>' . $lastPubDate->format('r') . '</pubDate>',
            '<lastBuildDate>' . $lastBuildDate->format('r') . '</lastBuildDate>',
            '<docs>https://blogs.law.harvard.edu/tech/rss</docs>',
            '<generator>' . $siteTitle . '</generator>',
            '<atom:link href="' . UrlBuilderUtil::buildPublicRssUrl() . '" rel="self" type="application/rss+xml" />',
        ];

        if ($siteAdminEmail && $siteAdminName) {
            $output[] = '<managingEditor>' . $siteAdminEmail . ' (' . $siteAdminName . ')</managingEditor>';
            $output[] = '<webMaster>' . $siteAdminEmail . ' (' . $siteAdminName . ')</webMaster>';
        }

        return $output;
    }

    private function buildRssContent(Language $siteLanguage, array $articles): array
    {
        $output = [];

        /** @var Article $article */
        foreach ($articles as $article) {
            $link = UrlBuilderUtil::buildPublicArticleUrl(
                uri: $article->uri,
                language: $siteLanguage,
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

    private function buildSitemapContent(array $articles): array
    {
        $output = [];

        /** @var Article $article */
        foreach ($articles as $article) {
            $output[] = '<url>';
            $output[] = '<loc>' . UrlBuilderUtil::buildPublicArticleUrl(uri: $article->uri) . '</loc>';
            $output[] = '<lastmod>' . $article->updatedAt->format('Y-m-d') . '</lastmod>';
            $output[] = '</url>';
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

    private function getSiteTitle(string $siteTitle, string $siteName): string
    {
        return $siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    private function getLastPubDate(?Article $article): DateTimeImmutable
    {
        if (!$article) {
            return new DateTimeImmutable('now');
        }

        return $article->publishOn;
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
