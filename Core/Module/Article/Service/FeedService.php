<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Router\AppRouter;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Module\Article\Entity\SitemapItem;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\Tag;
use Amora\Core\Util\LocalisationUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;
use DateTimeZone;

readonly class FeedService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function buildSitemap(array $sitemapItems): string
    {
        $this->logger->logInfo('Building sitemap...');

        $xml = array_merge(
            [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">',
            ],
            $this->buildSitemapContent(
                sitemapItems: $sitemapItems,
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

    public function buildJsonFeed(
        LocalisationUtil $localisationUtil,
        array $articles,
    ): string {
        $this->logger->logInfo('Building JSON Feed...');

        $output = $this->buildJsonFeedHeader($localisationUtil);
        $output['items'] = $this->buildJsonFeedContent(
            siteLanguage: $localisationUtil->language,
            articles: $articles,
        );

        $this->logger->logInfo('Building JSON Feed done.');

        return json_encode($output);
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
            $link = UrlBuilderUtil::buildPublicArticlePath(
                path: $article->path,
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

    private function buildSitemapContent(array $sitemapItems): array
    {
        $output = [];

        /** @var SitemapItem $sitemapItem */
        foreach ($sitemapItems as $sitemapItem) {
            $output[] = '<url>';
            $output[] = '<loc>' . $sitemapItem->fullPath . '</loc>';
            $output[] = '<lastmod>' . $sitemapItem->updatedAt->format('Y-m-d') . '</lastmod>';
            $output[] = '</url>';
        }

        foreach (AppRouter::getPublicReservedPaths() as $path) {
            $output[] = '<url>';
            $output[] = '<loc>' . UrlBuilderUtil::buildPublicArticlePath(path: $path) . '</loc>';
            $output[] = '</url>';
        }

        return $output;
    }

    private function buildJsonFeedHeader(LocalisationUtil $localisationUtil): array
    {
        $baseUrl = Core::getConfig()->baseUrl;
        $siteTitle = $this->getSiteTitle(
            siteTitle: $localisationUtil->getValue('siteTitle'),
            siteName: $localisationUtil->getValue('siteName'),
        );
        $siteDescription = $localisationUtil->getValue('siteDescription');
        $siteIcon = Core::getConfig()->siteIcon512pixels;
        $siteFavicon = Core::getConfig()->siteIcon64pixels;

        $output = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $siteTitle,
            'home_page_url' => $baseUrl,
            'feed_url' => UrlBuilderUtil::buildPublicJsonFeedUrl(),
            'description' => $siteDescription,
            'language' => strtolower($localisationUtil->language->value),
        ];

        if ($siteIcon) {
            $output['icon'] = rtrim($baseUrl, ' /') . $siteIcon;
        }

        if ($siteFavicon) {
            $output['favicon'] = rtrim($baseUrl, ' /') . $siteFavicon;
        }

        return $output;
    }

    private function buildJsonFeedContent(Language $siteLanguage, array $articles): array
    {
        $output = [];

        /** @var Article $article */
        foreach ($articles as $article) {
            $link = UrlBuilderUtil::buildPublicArticlePath(
                path: $article->path,
                language: $siteLanguage,
            );
            $title = $article->title ? htmlspecialchars($article->title) : '';
            $content = $this->getContent($article);

            $item = [
                'id' => $link,
                'url' => $link,
                'title' => $title,
                'content_html' => $content,
                'date_published' => $article->publishOn->format('c'),
                'language' => strtolower($article->language->value),
            ];

            if ($article->mainImage) {
                $item['image'] = $article->mainImage->getPathWithNameMedium();
            }

            if ($article->updatedAt > $article->publishOn) {
                $item['date_modified'] = $article->updatedAt->format('c');
            }

            $tags = [];
            /** @var Tag $tag */
            foreach ($article->tags as $tag) {
                $tags[] = $tag->name;
            }

            if ($tags) {
                $item['tags'] = $tags;
            }

            $output[] = $item;
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
