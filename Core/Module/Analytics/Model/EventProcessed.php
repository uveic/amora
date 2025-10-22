<?php

namespace Amora\Core\Module\Analytics\Model;

use Amora\Core\Module\Analytics\Value\EventType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

readonly class EventProcessed
{
    public function __construct(
        public int $rawId,
        public EventType $type,
        public int $userHashId,
        public int $urlId,
        public DateTimeImmutable $createdAt,
        public ?int $referrerId = null,
        public ?int $languageIsoCodeId = null,
        public ?int $platformId = null,
        public ?int $browserId = null,
    ) {
    }

    public function asArray(): array
    {
        return [
            'raw_id' => $this->rawId,
            'type_id' => $this->type->value,
            'user_hash_id' => $this->userHashId,
            'url_id' => $this->urlId,
            'referrer_id' => $this->referrerId,
            'language_iso_code_id' => $this->languageIsoCodeId,
            'platform_id' => $this->platformId,
            'browser_id' => $this->browserId,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
