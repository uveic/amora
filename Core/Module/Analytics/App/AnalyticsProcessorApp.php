<?php

namespace Amora\App\Module\Analytics\App;

use Amora\App\Router\AppRouter;
use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\UserAgentInfo;
use Amora\Core\Module\Analytics\Model\EventProcessed;
use Amora\Core\Module\Analytics\Model\EventRaw;
use Amora\Core\Module\Analytics\Service\AnalyticsService;
use Amora\Core\Module\Analytics\AnalyticsCore;
use Amora\Core\Module\Analytics\Value\BotUrl;
use Amora\Core\Module\Analytics\Value\BotUserAgent;
use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Util\Logger;
use Amora\Core\Util\NetworkUtil;
use DateTimeImmutable;
use UserAgentParserUtil;

class AnalyticsProcessorApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly AnalyticsService $analyticsService,
        private readonly string $siteUrl,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Analytics Processor',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run() {
        $this->execute(function () {
            $timeBefore = microtime(true);

            $entries = $this->analyticsService->getEntriesFromQueue();

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

        $userAgentInfo = UserAgentParserUtil::parse($eventRaw->userAgent);
        $languageIsoCode = $this->getLanguageIsoCode($eventRaw->clientLanguage);

        $eventType = $this->getEventType($eventRaw, $userAgentInfo);
        $referrer = $this->getReferrer($eventRaw);

        $res = $this->analyticsService->storeEventProcessed(
            new EventProcessed(
                id: null,
                rawId: $eventRaw->id,
                type: $eventType,
                createdAt: new DateTimeImmutable(),
                referrer: $referrer,
                languageIsoCode: $languageIsoCode,
                countryIsoCode: $countryIsoCode,
                city: $city,
                platform: $userAgentInfo->platform,
                browser: $userAgentInfo->browser,
                browserVersion: $userAgentInfo->version,
            )
        );

        if (!$res) {
            $this->logger->logInfo('Error processing event ID: ' . $eventRaw->id . '. Aborting...');
            return false;
        }

        $this->analyticsService->markEventAsProcessed($eventRaw->id);

        $this->logger->logInfo('Event ID (' . $eventRaw->id . ') successfully processed...');

        return true;
    }

    private function getEventType(EventRaw $event, UserAgentInfo $userAgentInfo): EventType
    {
        $apiActions = AppRouter::getApiActions();
        $parts = explode('/', $event->url);
        $action =$parts[0] ?? null;
        if (isset($apiActions[$action])) {
            return EventType::Api;
        }

        if (!empty($event->userId)) {
            return EventType::User;
        }

        if ($userAgentInfo->browser && BotUserAgent::isBot($userAgentInfo->browser)) {
            return EventType::Bot;
        }

        if (BotUrl::isBot($event->url)) {
            return EventType::Bot;
        }

        if (empty($event->clientLanguage)) {
            return EventType::ProbablyBot;
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

        if (str_contains($eventRaw->referrer, $this->siteUrl)) {
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
}
