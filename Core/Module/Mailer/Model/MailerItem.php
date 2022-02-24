<?php

namespace Amora\Core\Module\Mailer\Model;

use Amora\App\Value\Mailer\AppMailerTemplate;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class MailerItem
{
    public function __construct(
        public ?int $id,
        public readonly MailerTemplate $template,
        public readonly ?string $replyToEmailAddress,
        public readonly ?string $senderName,
        public readonly string $receiverEmailAddress,
        public readonly ?string $receiverName,
        public readonly ?string $subject,
        public readonly ?string $contentHtml,
        public readonly ?string $fieldsJson,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $processedAt = null,
        public readonly ?bool $hasError = null,
        public readonly ?string $lockId = null,
    ) {}

    public static function fromArray(array $item): MailerItem
    {
        /** @noinspection PhpCaseWithValueNotFoundInEnumInspection */
        $template = AppMailerTemplate::tryfrom($item['template_id'])
            ? AppMailerTemplate::from($item['template_id'])
            : MailerTemplate::from($item['template_id']);

        return new MailerItem(
            id: (int)$item['mail_id'],
            template: $template,
            replyToEmailAddress: $item['reply_to_email'] ?? null,
            senderName: $item['sender_name'] ?? null,
            receiverEmailAddress: $item['receiver_email'],
            receiverName: $item['receiver_name'] ?? null,
            subject: $item['subject'] ?? null,
            contentHtml: $item['content_html'] ?? null,
            fieldsJson: $item['fields_json'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($item['created_at']),
            processedAt: isset($item['processed_at'])
                ? DateUtil::convertStringToDateTimeImmutable($item['processed_at'])
                : null,
            hasError: isset($item['has_error']) ? !empty($item['has_error']) : null,
            lockId: $item['lock_id'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->template->value,
            'reply_to_email' => $this->replyToEmailAddress,
            'sender_name' => $this->senderName,
            'receiver_email' => $this->receiverEmailAddress,
            'receiver_name' => $this->receiverName,
            'subject' => $this->subject,
            'content_html' => $this->contentHtml,
            'fields_json' => $this->fieldsJson,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'processed_at' => $this->processedAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'has_error' => $this->hasError,
            'lock_id' => $this->lockId,
        ];
    }
}
