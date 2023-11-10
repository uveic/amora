<?php

namespace Amora\Core\Module\Article\Service;

use Amora\App\Router\AppRouter;
use Amora\Core\Core;
use Amora\Core\Module\Article\Entity\FeedItem;
use Amora\Core\Util\Logger;
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

    public function buildSitemap(array $feedItems): string
    {
        $this->logger->logInfo('Building sitemap...');

        $xml = array_merge(
            [
                '<?xml version="1.0" encoding="UTF-8"?>',
                '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">',
            ],
            $this->buildSitemapContent(
                feedItems: $feedItems,
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
        array $feedItems,
    ): string {
        $this->logger->logInfo('Building RSS...');

        $lastBuildDate = $this->getBuildDate($feedItems);

        $xml = array_merge(
            [
                '<?xml version="1.0"?>',
                '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
                '<channel>',
            ],
            $this->buildRssHeader(
                localisationUtil: $localisationUtil,
                lastPubDate: $this->getLastPubDate($feedItems[0] ?? null),
                lastBuildDate: $lastBuildDate,
            ),
            $this->buildRssContent($feedItems),
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
        array $feedItems,
    ): string {
        $this->logger->logInfo('Building JSON Feed...');

        $output = $this->buildJsonFeedHeader($localisationUtil);
        $output['items'] = $this->buildJsonFeedContent($feedItems);

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

    private function buildRssContent(array $feedItems): array
    {
        $output = [];

        /** @var FeedItem $feedItem */
        foreach ($feedItems as $feedItem) {
            $link = $feedItem->fullPath;
            $title = $feedItem->title ? htmlspecialchars($feedItem->title) : '';
            $content = $this->getContent($feedItem);

            $output[] = '<item>';
            $output[] = '<title>' . $title . '</title>';
            $output[] = '<link>' . $link . '</link>';
            $output[] = '<guid>' . $link . '</guid>';
            if ($feedItem->user) {
                $output[] = '<author>' . $feedItem->user->email . ' (' . $feedItem->user->name . ')</author>';
            }
            $output[] = '<description>' . $content . '</description>';
            $output[] = '<pubDate>' . $feedItem->publishedOn->format('r') . '</pubDate>';

            /** @var Tag $tag */
            foreach ($feedItem->tags as $tag) {
                $output[] = '<category>' . $tag->name . '</category>';
            }

            $output[] = '</item>';
        }

        return $output;
    }

    private function buildSitemapContent(array $feedItems): array
    {
        $output = [];

        /** @var FeedItem $feedItem */
        foreach ($feedItems as $feedItem) {
            $output[] = '<url>';
            $output[] = '<loc>' . $feedItem->fullPath . '</loc>';
            $output[] = '<lastmod>' . $feedItem->updatedAt->format('Y-m-d') . '</lastmod>';
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

    private function buildJsonFeedContent(array $feedItems): array
    {
        $baseUrl = Core::getConfig()->baseUrl;

        $output = [];

        /** @var FeedItem $feedItem */
        foreach ($feedItems as $feedItem) {
            $title = $feedItem->title ? htmlspecialchars($feedItem->title) : '';
            $content = $this->getContent($feedItem);

            $item = [
                'id' => $feedItem->fullPath,
                'url' => $feedItem->fullPath,
                'title' => $title,
                'content_html' => $content,
                'date_published' => $feedItem->publishedOn->format('c'),
                'language' => strtolower($feedItem->language->value),
            ];

            if ($feedItem->media) {
                $item['image'] = rtrim($baseUrl, ' /') . $feedItem->media->getPathWithNameMedium();
            }

            if ($feedItem->updatedAt > $feedItem->media) {
                $item['date_modified'] = $feedItem->updatedAt->format('c');
            }

            $tags = [];
            /** @var Tag $tag */
            foreach ($feedItem->tags as $tag) {
                $tags[] = $tag->name;
            }

            if ($tags) {
                $item['tags'] = $tags;
            }

            $output[] = $item;
        }

        return $output;
    }

    private function getContent(FeedItem $feedItem): string
    {
        return htmlspecialchars(
            str_replace(
                search: 'src="/',
                replace: 'src="' . UrlBuilderUtil::buildBaseUrlWithoutLanguage() . '/',
                subject: $feedItem->contentHtml,
            )
        );
    }

    private function getSiteTitle(string $siteTitle, string $siteName): string
    {
        return $siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }

    private function getLastPubDate(?FeedItem $feedItem): DateTimeImmutable
    {
        if (!$feedItem) {
            return new DateTimeImmutable('now');
        }

        return $feedItem->publishedOn;
    }

    private function getBuildDate(array $feedItems): DateTimeImmutable
    {
        if (!$feedItems) {
            return new DateTimeImmutable();
        }

        $buildDate = $this->getLastPubDate($feedItems[0]);

        /** @var FeedItem $feedItem */
        foreach ($feedItems as $feedItem) {
            $updatedAt = $feedItem->publishedOn;
            if ($buildDate < $updatedAt) {
                $buildDate = $updatedAt;
            }
        }

        return $buildDate->setTimezone(new DateTimeZone('UTC'));
    }
}
