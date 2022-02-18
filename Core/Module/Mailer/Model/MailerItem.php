<?php

namespace Amora\Core\Module\Mailer\Model;

use Amora\Core\Module\Mailer\Value\MailerTemplate;
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
        $id = empty($item['id'])
            ? (empty($item['mail_id']) ? null : $item['mail_id'])
            : (int)$item['id'];

        return new MailerItem(
            id: $id,
            template: $item['template_id'],
            replyToEmailAddress: $item['reply_to_email'] ?? null,
            senderName: $item['sender_name'] ?? null,
            receiverEmailAddress: $item['receiver_email'],
            receiverName: $item['receiver_name'] ?? null,
            subject: $item['subject'] ?? null,
            contentHtml: $item['content_html'] ?? null,
            fieldsJson: $item['fields_json'] ?? null,
            createdAt: $item['created_at'],
            processedAt: $item['processed_at'] ?? null,
            hasError: isset($item['has_error']) ? !empty($item['has_error']) : null,
            lockId: $item['lock_id'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'template_id' => $this->getTemplateId(),
            'reply_to_email' => $this->getReplyToEmailAddress(),
            'sender_name' => $this->getSenderName(),
            'receiver_email' => $this->getReceiverEmailAddress(),
            'receiver_name' => $this->getReceiverName(),
            'subject' => $this->getSubject(),
            'content_html' => $this->getContentHtml(),
            'fields_json' => $this->getFieldsJson(),
            'created_at' => $this->getCreatedAt(),
            'processed_at' => $this->getProcessedAt(),
            'has_error' => $this->getHasError(),
            'lock_id' => $this->getLockId()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function getReplyToEmailAddress(): ?string
    {
        return $this->replyToEmailAddress;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function getReceiverEmailAddress(): string
    {
        return $this->receiverEmailAddress;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function getFieldsJson(): ?string
    {
        return $this->fieldsJson;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?string
    {
        return $this->processedAt;
    }

    public function getHasError(): ?bool
    {
        return $this->hasError;
    }

    public function getLockId(): ?string
    {
        return $this->lockId;
    }
}
