<?php

namespace Amora\Core\Module\Analytics\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class EventRaw
{
    public function __construct(
        public ?int $id,
        public readonly ?int $userId,
        public readonly ?string $sessionId,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?string $url,
        public readonly ?string $referrer,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $clientLanguage,
        public readonly ?string $searchQuery = null,
        public readonly ?DateTimeImmutable $processedAt = null,
        public readonly ?string $lockId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['event_raw_id']) ? (int)$data['event_raw_id'] : null,
            userId: isset($data['event_raw_user_id']) ? (int)$data['event_raw_user_id'] : null,
            sessionId: $data['event_raw_session_id'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['event_raw_created_at']),
            url: $data['event_raw_url'],
            referrer: $data['event_raw_referrer'] ?? null,
            ip: $data['event_raw_ip'] ?? null,
            userAgent: $data['event_raw_user_agent'] ?? null,
            clientLanguage: $data['event_raw_client_language'] ?? null,
            searchQuery: $data['event_raw_search_query'] ?? null,
            processedAt: isset($data['event_raw_processed_at'])
                ? DateUtil::convertStringToDateTimeImmutable($data['event_raw_processed_at'])
                : null,
            lockId: $data['event_raw_lock_id'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'url' => $this->url,
            'referrer' => $this->referrer,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'client_language' => $this->clientLanguage,
            'search_query' => $this->searchQuery,
            'processed_at' => $this->processedAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'lock_id' => $this->lockId,
        ];
    }
}
