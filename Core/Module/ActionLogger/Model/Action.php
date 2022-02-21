<?php

namespace Amora\Core\Module\Action\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class Action
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $userId,
        public readonly ?string $sessionId,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $url,
        public readonly ?string $referrer = null,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $clientLanguage = null
    ) {}

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
        ];
    }
}
