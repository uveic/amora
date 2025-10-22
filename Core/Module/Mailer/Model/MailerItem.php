<?php

namespace Amora\Core\Module\Mailer\Model;

use Amora\App\Value\AppMailerTemplate;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class MailerItem
{
    public function __construct(
        public ?int $id,
        public readonly MailerTemplate|AppMailerTemplate $template,
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
    ) {
    }

    public static function fromArray(array $item): MailerItem
    {
        $template = AppMailerTemplate::tryfrom($item['mailer_item_template_id'])
            ? AppMailerTemplate::from($item['mailer_item_template_id'])
            : MailerTemplate::from($item['mailer_item_template_id']);

        return new MailerItem(
            id: (int)$item['mailer_item_id'],
            template: $template,
            replyToEmailAddress: $item['mailer_item_reply_to_email'] ?? null,
            senderName: $item['mailer_item_sender_name'] ?? null,
            receiverEmailAddress: $item['mailer_item_receiver_email'],
            receiverName: $item['mailer_item_receiver_name'] ?? null,
            subject: $item['mailer_item_subject'] ?? null,
            contentHtml: $item['mailer_item_content_html'] ?? null,
            fieldsJson: $item['mailer_item_fields_json'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($item['mailer_item_created_at']),
            processedAt: isset($item['mailer_item_processed_at'])
                ? DateUtil::convertStringToDateTimeImmutable($item['mailer_item_processed_at'])
                : null,
            hasError: isset($item['mailer_item_has_error']) ? !empty($item['mailer_item_has_error']) : null,
            lockId: $item['mailer_item_lock_id'] ?? null,
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
