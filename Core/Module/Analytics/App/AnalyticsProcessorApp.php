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
use Amora\Core\Module\Analytics\Model\EventValue;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Module\Analytics\Value\Parameter;
use Amora\Core\Util\Logger;
use Amora\Core\Util\UserAgentParserUtil;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class AnalyticsProcessorApp extends App
{
    private array $botPath = [];
    private array $botUserAgent = [];

    private array $valueReferrer = [];
    private array $valueUserHash = [];
    private array $valueUrl = [];
    private array $valueLanguageIsoCode = [];
    private array $valuePlatform = [];
    private array $valueBrowser = [];

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

    public function run(?int $totalEntries = null): void
    {
        $this->execute(function () use ($totalEntries) {
            $timeBefore = microtime(true);

            $entries = $this->getEntriesFromQueue($totalEntries ?? 5000);

            /** @var EventRaw $firstEntry */
            $firstEntry = $entries[0] ?? null;

            if (!$firstEntry) {
                return;
            }

            $this->loadBotsMapping();
            $this->loadValuesMapping();

            $this->log("First entry ID: $firstEntry->id ::: Processing " . count($entries) . " entries...");

            $storedRawIds = [];

            /** @var EventRaw $entry */
            foreach ($entries as $entry) {
                $res = AnalyticsCore::getDb()->withTransaction(
                    function () use ($entry) {
                        $rawId = $this->processRawEvent($entry);

                        return new Feedback(
                            isSuccess: true,
                            response: $rawId
                        );
                    }
                );

                if (!$res->isSuccess) {
                    $this->log('Something went wrong. Aborting...', true);
                    exit;
                }

                if ($res->response) {
                    $storedRawIds[] = $res->response;
                }
            }

            $this->deleteProcessed($storedRawIds);

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $totalEntries = count($entries);
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;

            $this->log($totalEntries . ' entries processed.' . (isset($entry) ? (' Last entry ID: ' . $entry->id) : ''));
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processRawEvent(EventRaw $eventRaw): ?int
    {
        $userAgentInfo = $this->getUserAgentInfo($eventRaw->userAgent);
        $languageIsoCode = $this->getLanguageIsoCode($eventRaw->clientLanguage);
        $eventType = $this->getEventType($eventRaw, $userAgentInfo, $languageIsoCode);

        $eventProcessed = new EventProcessed(
            rawId: $eventRaw->id,
            type: $eventType,
            userHashId: $this->retrieveOrStoreEventValue(Parameter::VisitorHash, $eventRaw),
            urlId: $this->retrieveOrStoreEventValue(Parameter::Url, $eventRaw),
            createdAt: $eventRaw->createdAt,
            referrerId: $this->retrieveOrStoreEventValue(Parameter::Referrer, $eventRaw),
            languageIsoCodeId: $this->retrieveOrStoreEventValue(Parameter::Language, $eventRaw),
            platformId: $this->retrieveOrStoreEventValue(Parameter::Platform, $eventRaw, $userAgentInfo),
            browserId: $this->retrieveOrStoreEventValue(Parameter::Browser, $eventRaw, $userAgentInfo),
        );

        $this->analyticsDataLayer->storeEventProcessed($eventProcessed);

        if ($eventRaw->searchQuery) {
            $this->analyticsDataLayer->storeSearch(
                rawId: $eventRaw->id,
                searchQuery: $eventRaw->searchQuery,
            );
        }

        $this->analyticsDataLayer->markEventAsProcessed($eventRaw->id);

        return $eventType->deleteAfterProcessing() ? $eventRaw->id : null;
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

    private function retrieveOrStoreEventValue(
        Parameter $parameter,
        EventRaw $eventRaw,
        ?UserAgentInfo $userAgentInfo = null,
    ): ?int {
        $value = match ($parameter) {
            Parameter::Url => $eventRaw->url ?: '/',
            Parameter::Platform => $userAgentInfo?->platform,
            Parameter::Browser => $userAgentInfo?->browser,
            Parameter::Language => $this->getLanguageIsoCode($eventRaw->clientLanguage),
            Parameter::Referrer => $this->getReferrer($eventRaw),
            Parameter::VisitorHash => $this->getUserHash($eventRaw),
        };

        if (empty($value)) {
            return null;
        }

        $value = substr($value, 0, $parameter->getDbColumnMaxLength());

        $existing = match ($parameter) {
            Parameter::Url => $this->valueUrl[$value] ?? null,
            Parameter::Platform => $this->valuePlatform[$value] ?? null,
            Parameter::Browser => $this->valueBrowser[$value] ?? null,
            Parameter::Language => $this->valueLanguageIsoCode[$value] ?? null,
            Parameter::Referrer => $this->valueReferrer[$value] ?? null,
            Parameter::VisitorHash => $this->valueUserHash[$value] ?? null,
        };

        if ($existing) {
            return $existing;
        }

        $existing = $this->analyticsDataLayer->filterEventValueBy(
            parameter: $parameter,
            value: $value,
        );

        if ($existing) {
            return $existing->id;
        }

        $newEventValue = $this->analyticsDataLayer->storeEventValue(
            item: new EventValue(
                id: null,
                value: $value,
            ),
            parameter: $parameter,
        );

        if ($parameter === Parameter::Url) {
            $this->valueUrl[$value] = $newEventValue->id;
        } elseif ($parameter === Parameter::Platform) {
            $this->valuePlatform[$value] = $newEventValue->id;
        } elseif ($parameter === Parameter::Browser) {
            $this->valueBrowser[$value] = $newEventValue->id;
        } elseif ($parameter === Parameter::Language) {
            $this->valueLanguageIsoCode[$value] = $newEventValue->id;
        } elseif ($parameter === Parameter::Referrer) {
            $this->valueReferrer[$value] = $newEventValue->id;
        } elseif ($parameter === Parameter::VisitorHash) {
            $this->valueUserHash[$value] = $newEventValue->id;
        }

        return $newEventValue->id;
    }

    private function deleteProcessed(array $storedRawIds): void
    {
        if (!$storedRawIds) {
            return;
        }

        $this->log('Deleting stored raw IDs...');

        $itemsCount = 1000;
        $count = 0;

        while ($rawIds = array_slice($storedRawIds, $count * $itemsCount, $itemsCount)) {
            $res = $this->analyticsDataLayer->destroyRawEvent($rawIds);
            if (!$res) {
                $this->log('Error deleting stored raw IDs', true);
            }

            $count++;
        }

        $res = $this->analyticsDataLayer->destroyOldEvents();
        if (!$res) {
            $this->log('Error deleting ancient raw events', true);
        }
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

        if (array_any($startsWith, static fn($value) => str_starts_with($item, $value))) {
            return true;
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
            '.env',
        ];

        if (array_any($endsWith, static fn($value) => str_ends_with($item, $value))) {
            return true;
        }

        return $this->botPath[$item] ?? false;
    }

    private function isCrawler(?string $item, ?string $userAgentRaw): bool
    {
        if ($item && isset($this->botUserAgent[$item])) {
            return true;
        }

        $CrawlerDetect = new CrawlerDetect();
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

        if ($output->browser && strlen($output->browser) < 3) {
            $output = new UserAgentInfo(
                platform: $output->platform,
                browser: null,
                version: $output->version,
            );
        }

        if ($output->browser) {
            return $output;
        }

        $CrawlerDetect = new CrawlerDetect();
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

    private function loadValuesMapping(): void
    {
        $this->log('Loading event values...');

        if (empty($this->valueLanguageIsoCode)) {
            $this->valueLanguageIsoCode = $this->analyticsDataLayer->loadValues(Parameter::Language);
        }

        if (empty($this->valueReferrer)) {
            $this->valueReferrer = $this->analyticsDataLayer->loadValues(Parameter::Referrer);
        }

        if (empty($this->valuePlatform)) {
            $this->valuePlatform = $this->analyticsDataLayer->loadValues(Parameter::Platform);
        }

        if (empty($this->valueBrowser)) {
            $this->valueBrowser = $this->analyticsDataLayer->loadValues(Parameter::Browser);
        }
    }

    private function getEntriesFromQueue(int $qty): array
    {
        $this->log('Releasing locks...');
        $this->analyticsDataLayer->releaseQueueLocksIfNeeded();

        $this->log('Checking for locked entries...');
        $lockedEntries = $this->analyticsDataLayer->getLockedEntriesCount();
        if ($lockedEntries) {
            $this->log('There are (' . $lockedEntries . ') entries(s) locked. Aborting...');

            return [];
        }

        $lockId = $this->analyticsDataLayer->getUniqueLockId();

        $res = $this->analyticsDataLayer->lockQueueEntries(lockId: $lockId, qty: $qty);
        if (empty($res)) {
            $this->logger->logError('Error locking entries. Aborting...');
            return [];
        }

        $this->log('Getting entries to process...');
        return $this->analyticsDataLayer->getEntriesFromQueue(lockId: $lockId, qty: $qty);
    }
}
