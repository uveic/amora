<?php

namespace Amora\App\Module\Analytics\App;

use Amora\App\Router\AppRouter;
use Amora\App\Value\Language;
use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\UserAgentInfo;
use Amora\Core\Module\Analytics\Datalayer\AnalyticsDataLayer;
use Amora\Core\Module\Analytics\Model\EventProcessed;
use Amora\Core\Module\Analytics\Model\EventRaw;
use Amora\Core\Module\Analytics\AnalyticsCore;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Util\Logger;
use Amora\Core\Util\NetworkUtil;
use DateTimeImmutable;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use UserAgentParserUtil;

class AnalyticsProcessorApp extends App
{
    private array $botPath = [];
    private array $botUserAgent = [];

    public function __construct(
        Logger $logger,
        private readonly AnalyticsDataLayer $analyticsDataLayer,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Analytics Processor',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run(): void
    {
        $this->execute(function () {
            $timeBefore = microtime(true);

            $this->loadBotsMapping();
            $entries = $this->getEntriesFromQueue();

            /** @var EventRaw $entry */
            foreach ($entries as $entry) {
                $res = AnalyticsCore::getDb()->withTransaction(
                    function() use ($entry) {
                        $res = $this->processRawEvent($entry);

                        return new Feedback($res);
                    }
                );

                if (!$res->isSuccess) {
                    $this->log('Something went wrong. Aborting...', true);
                    exit;
                }
            }

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $totalEntries = count($entries);
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;

            $this->log($totalEntries . ' entries processed.');
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processRawEvent(EventRaw $eventRaw): bool
    {
        $countryIsoCode = $eventRaw->ip
            ? NetworkUtil::getCountryCodeFromIP($eventRaw->ip)
            : null;

        $city = $eventRaw->ip
            ? NetworkUtil::getCityFromIP($eventRaw->ip)
            : null;

        $userAgentInfo = $this->getUserAgentInfo($eventRaw->userAgent);
        $languageIsoCode = $this->getLanguageIsoCode($eventRaw->clientLanguage);

        $eventType = $this->getEventType($eventRaw, $userAgentInfo, $languageIsoCode);
        $referrer = $this->getReferrer($eventRaw);
        $userHash = $this->getUserHash($eventRaw);

        $res = $this->analyticsDataLayer->storeEventProcessed(
            event: new EventProcessed(
                id: null,
                rawId: $eventRaw->id,
                userHash: $userHash,
                type: $eventType,
                createdAt: new DateTimeImmutable(),
                referrer: $referrer,
                languageIsoCode: $languageIsoCode,
                countryIsoCode: $countryIsoCode,
                city: $city,
                platform: $userAgentInfo->platform,
                browser: $userAgentInfo->browser,
                browserVersion: $userAgentInfo->version,
            ),
        );

        if (!$res) {
            $this->log('Error processing event ID: ' . $eventRaw->id . '. Aborting...');
            return false;
        }

        $this->analyticsDataLayer->markEventAsProcessed($eventRaw->id);

        $this->log('Event ID (' . $eventRaw->id . ') successfully processed...');

        return true;
    }

    private function getEventType(
        EventRaw $event,
        UserAgentInfo $userAgentInfo,
        ?string $languageIsoCode,
    ): EventType {
        $apiActions = AppRouter::getApiActions();
        $action = $this->getActionFromPath($event->url);
        if (isset($apiActions[$action])) {
            return EventType::Api;
        }

        if ($event->userId) {
            return EventType::User;
        }

        if ($this->isCrawler($userAgentInfo->browser, $event->userAgent)) {
            return EventType::Crawler;
        }

        if ($event->url && $this->isBot($event->url)) {
            return EventType::Bot;
        }

        if (!$this->isValidClientLanguage($languageIsoCode)) {
            return EventType::Bot;
        }

        return EventType::Visitor;
    }

    private function getLanguageIsoCode(?string $clientLanguage): ?string
    {
        if (empty($clientLanguage)) {
            return null;
        }

        $parts = explode(',', $clientLanguage);

        foreach ($parts as $part) {
            $semicolon = strpos($part, ';');
            $lang = $semicolon !== false
                ? substr($part, 0, $semicolon)
                : $part;

            $dash = strpos($lang, '-');
            $lang = $dash !== false ? substr($lang, 0, $dash) : $lang;

            $dot = strpos($lang, '.');
            $lang = $dot !== false ? substr($lang, 0, $dot) : $lang;

            return strtoupper(substr($lang, 0, 3));
        }

        return null;
    }

    private function getReferrer(EventRaw $eventRaw): ?string
    {
        if (empty($eventRaw->referrer)) {
            return null;
        }

        $host = parse_url($eventRaw->referrer, PHP_URL_HOST);
        if ($host) {
            $host = strtolower($host);
            if (str_starts_with($host, 'www.')) {
                $host = substr($host, 4);
            }

            return $host;
        }

        return null;
    }

    private function getActionFromPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $arrayPath = explode('/', $path);
        if (!empty($arrayPath[0]) && strlen($arrayPath[0]) === 2) {
            $uppercaseLanguage = strtoupper($arrayPath[0]);
            if (Language::tryFrom($uppercaseLanguage)) {
                unset($arrayPath[0]);
            }
        }

        return array_values($arrayPath)[0] ?? null;
    }

    private function getUserHash(EventRaw $eventRaw): string
    {
        if ($eventRaw->sessionId) {
            return md5($eventRaw->sessionId);
        }

        return md5($eventRaw->ip . $eventRaw->userAgent . $eventRaw->clientLanguage);
    }

    private function isBot(string $item): bool
    {
        if (empty($item)) {
            return false;
        }
        $item = strtolower($item);

        $startsWith = [
            'wp-content/',
            'wp-admin/',
            'wp-includes/',
            '.well-known',
            'wp-json',
            'index.php',
            'php-cgi',
            'phpmyadmin',
            'mysql',
            'myadmin',
        ];

        foreach ($startsWith as $value) {
            if (str_starts_with($item, $value)) {
                return true;
            }
        }

        $endsWith = [
            '.php',
            '.txt',
            '.css',
            '.js',
            '.zip',
            '.sql',
            '.exe',
            '.xml',
            '.cgi',
            '.do',
            '.json',
            '.bak',
            '.xsd',
            '.html',
            '.ts',
            '.yml',
            '.yaml',
            '.py',
            '.conf',
            '.log',
            '.ini',
            '.rb',
            '.md',
        ];

        foreach ($endsWith as $value) {
            if (str_ends_with($item, $value)) {
                return true;
            }
        }

        return $this->botPath[$item] ?? false;
    }

    private function isCrawler(?string $item, ?string $userAgentRaw): bool
    {
        if ($item && isset($this->botUserAgent[$item])) {
            return true;
        }

        $CrawlerDetect = new CrawlerDetect;
        if ($CrawlerDetect->isCrawler($userAgentRaw)) {
            return true;
        }

        return false;
    }

    private function isValidClientLanguage(?string $languageIsoCode): bool
    {
        if (empty($languageIsoCode)) {
            return false;
        }

        $length = strlen($languageIsoCode);

        if ($length !== 2 && $length !== 3) {
            return false;
        }

        return true;
    }

    private function getUserAgentInfo(?string $userAgentRaw): UserAgentInfo
    {
        if (!$userAgentRaw) {
            return new UserAgentInfo();
        }

        $output = UserAgentParserUtil::parse($userAgentRaw);

        if ($output->browser) {
            return $output;
        }

        $CrawlerDetect = new CrawlerDetect;
        if ($CrawlerDetect->isCrawler($userAgentRaw)) {
            return new UserAgentInfo(
                platform: $output->platform,
                browser: $CrawlerDetect->getMatches() ?: null,
                version: $output->version,
            );
        }

        return $output;
    }

    private function loadBotsMapping(): void
    {
        if (empty($this->botPath)) {
            $this->log('Loading bot paths...');
            $this->botPath = $this->analyticsDataLayer->loadBotPaths();
        }

        if (empty($this->botUserAgent)) {
            $this->log('Loading bot user agents...');
            $this->botUserAgent = $this->analyticsDataLayer->loadBotUserAgents();
        }
    }

    private function getEntriesFromQueue(): array
    {
        $this->log('Releasing locks...');
        $this->analyticsDataLayer->releaseQueueLocksIfNeeded();

        $this->log('Checking for locked entries...');
        $lockedEntries = $this->analyticsDataLayer->getNumberOfLockedEntries();
        if ($lockedEntries) {
            $this->log('There are (' . $lockedEntries . ') entries(s) locked. Aborting...'
            );

            return [];
        }

        $lockId = $this->analyticsDataLayer->getUniqueLockId();

        $res = $this->analyticsDataLayer->lockQueueEntries(lockId: $lockId, qty: 5000);
        if (empty($res)) {
            $this->logger->logError('Error locking entries. Aborting...');
            return [];
        }

        $this->log('Getting entries to process...');
        return $this->analyticsDataLayer->getEntriesFromQueue($lockId);
    }
}
