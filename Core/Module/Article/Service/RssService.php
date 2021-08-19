<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Util\LocalisationUtil;
use DateTimeImmutable;
use DateTimeZone;
use SimpleXMLElement;

class RssService
{
    protected LocalisationUtil $localisationUtil;

    public function __construct(
        private Logger $logger,
    ) {}

    public function buildRss(
        string $siteLanguage,
    ): SimpleXMLElement {
        $this->localisationUtil = Core::getLocalisationUtil(strtoupper($siteLanguage));

        $this->logger->logInfo('Building RSS...');

        $xml = array_merge(
            [
                '<?xml version="1.0"?>',
                '<rss version="2.0">',
                '<channel>',
            ],
            $this->buildMainItems(
                siteLanguage: $siteLanguage,
                lastPubDate: new DateTimeImmutable('now'),
            ),
            $this->buildContent(),
            [
                '</channel>',
                '</rss>',
            ]
        );

        return new SimpleXMLElement(implode('', $xml), LIBXML_NOCDATA);
    }

    private function buildMainItems(
        string $siteLanguage,
        DateTimeImmutable $lastPubDate,
    ): array {
        $baseUrl = Core::getConfigValue('baseUrl');
        $siteAdminEmail = Core::getConfigValue('siteAdminEmail');
        $siteTitle = $this->getSiteTitle();
        $siteDescription = $this->localisationUtil->getValue('siteDescription');

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $output = [
            '<title>' . $siteTitle . '</title>',
            '<link>' . $baseUrl . '</link>',
            '<description>' . $siteDescription . '</description>',
            '<language>' . strtolower($siteLanguage) . '</language>',
            '<pubDate>' . $lastPubDate->format('r') . '</pubDate>',
            '<lastBuildDate>' . $now->format('r') . '</lastBuildDate>',
            '<docs>http://blogs.law.harvard.edu/tech/rss</docs>',
            '<generator>' . $siteTitle . '</generator>',
        ];

        if ($siteAdminEmail) {
            $output[] = '<managingEditor>' . $siteAdminEmail . '</managingEditor>';
            $output[] = '<webMaster>' . $siteAdminEmail . '</webMaster>';
        }

        return $output;
    }

    private function buildContent(): array
    {
        return [
            '<item>',
            '<description>Sky watchers in Europe, Asia, and parts of Alaska and Canada will experience a &lt;a href="http://science.nasa.gov/headlines/y2003/30may_solareclipse.htm"&gt;partial eclipse of the Sun&lt;/a&gt; on Saturday, May 31st.</description>',
            '<pubDate>Fri, 30 May 2003 11:06:42 GMT</pubDate>',
            '<guid>http://liftoff.msfc.nasa.gov/2003/05/30.html#item572</guid>',
            '</item>',
        ];
    }

    private function getSiteTitle(): string
    {
        $siteTitle = $this->localisationUtil->getValue('siteTitle');
        $siteName = $this->localisationUtil->getValue('siteName');

        return $siteName . ($siteTitle ? ' - ' . $siteTitle : '');
    }
}
