<?php

namespace Amora\Core\Module\Analytics\Value;

use Amora\Core\Module\Analytics\Datalayer\AnalyticsDataLayer;

enum Parameter: int
{
    case Url = 1;
    case Platform = 3;
    case Browser = 4;
    case Language = 5;
    case Referrer = 6;
    case VisitorHash = 7;

    public static function getAll(): array
    {
        return [
            self::Url,
            self::Platform,
            self::Browser,
            self::Language,
            self::Referrer,
            self::VisitorHash,
        ];
    }

    public function getValueTableName(): string
    {
        return match ($this) {
            self::Url => AnalyticsDataLayer::EVENT_VALUE_URL,
            self::Platform => AnalyticsDataLayer::EVENT_VALUE_USER_AGENT_PLATFORM,
            self::Browser => AnalyticsDataLayer::EVENT_VALUE_USER_AGENT_BROWSER,
            self::Language => AnalyticsDataLayer::EVENT_VALUE_LANGUAGE_ISO_CODE,
            self::Referrer => AnalyticsDataLayer::EVENT_VALUE_REFERRER,
            self::VisitorHash => AnalyticsDataLayer::EVENT_VALUE_USER_HASH,
        };
    }

    public function getColumnName(): string
    {
        return match ($this) {
            self::Url => 'url_id',
            self::Platform => 'platform_id',
            self::Browser => 'browser_id',
            self::Language => 'language_iso_code_id',
            self::Referrer => 'referrer_id',
            self::VisitorHash => 'user_hash_id',
        };
    }

    public function getDbColumnMaxLength(): int
    {
        return match ($this) {
            self::Url => 150,
            self::Platform, self::Browser => 40,
            self::Language => 3,
            self::Referrer => 50,
            self::VisitorHash => 32,
        };
    }
}
