<?php

namespace Amora\Core\Module\Analytics\Model;

use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class EventProcessed
{
    public function __construct(
        public ?int $id,
        public readonly int $rawId,
        public readonly EventType $type,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?string $referrer = null,
        public readonly ?string $languageIsoCode = null,
        public readonly ?string $countryIsoCode = null,
        public readonly ?string $city = null,
        public readonly ?string $platform = null,
        public readonly ?string $browser = null,
        public readonly ?string $browserVersion = null,
    ) {}

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'raw_id' => $this->rawId,
            'type_id' => $this->type->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'referrer' => $this->referrer,
            'language_iso_code' => $this->languageIsoCode,
            'country_iso_code' => $this->countryIsoCode,
            'city' => $this->city,
            'user_agent_platform' => $this->platform,
            'user_agent_browser' => $this->browser,
            'user_agent_version' => $this->browserVersion,
        ];
    }
}
