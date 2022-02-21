<?php

namespace Amora\Core\Module\Mailer\Model;

use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class MailerLogItem
{
    public function __construct(
        public ?int $id,
        public readonly int $mailerQueueId,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $request,
        public readonly ?string $response = null,
        public readonly ?bool $sent = null,
        public readonly ?string $errorMessage = null
    ) {}

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'mailer_queue_id' => $this->mailerQueueId,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'request' => $this->request,
            'response' => $this->response,
            'sent' => $this->sent,
            'error_message' => $this->errorMessage,
        ];
    }
}
