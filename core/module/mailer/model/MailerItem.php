<?php

namespace uve\core\module\mailer\model;

class MailerItem
{
    private ?int $id;
    private int $templateId;
    private ?string $replyToEmailAddress;
    private ?string $senderName;
    private string $receiverEmailAddress;
    private ?string $receiverName;
    private ?string $subject;
    private ?string $contentHtml;
    private ?string $fieldsJson;
    private string $createdAt;
    private ?string $processedAt;
    private ?bool $hasError;
    private ?string $lockId;

    public function __construct(
        ?int $id,
        int $templateId,
        ?string $replyToEmailAddress,
        ?string $senderName,
        string $receiverEmailAddress,
        ?string $receiverName,
        ?string $subject,
        ?string $contentHtml,
        ?string $fieldsJson,
        string $createdAt,
        ?string $processedAt = null,
        ?bool $hasError = null,
        ?string $lockId = null
    ) {
        $this->id = $id;
        $this->templateId = $templateId;
        $this->replyToEmailAddress = $replyToEmailAddress;
        $this->senderName = $senderName;
        $this->receiverEmailAddress = $receiverEmailAddress;
        $this->receiverName = $receiverName;
        $this->subject = $subject;
        $this->contentHtml = $contentHtml;
        $this->fieldsJson = $fieldsJson;
        $this->createdAt = $createdAt;
        $this->processedAt = $processedAt;
        $this->hasError = $hasError;
        $this->lockId = $lockId;
    }

    public static function fromArray(array $item): MailerItem
    {
        $id = empty($item['id'])
            ? (empty($item['mail_id']) ? null : $item['mail_id'])
            : (int)$item['id'];

        return new MailerItem(
            $id,
            $item['template_id'],
            $item['reply_to_email'] ?? null,
            $item['sender_name'] ?? null,
            $item['receiver_email'],
            $item['receiver_name'] ?? null,
            $item['subject'] ?? null,
            $item['content_html'] ?? null,
            $item['fields_json'] ?? null,
            $item['created_at'],
            $item['processed_at'] ?? null,
            isset($item['has_error']) ? (empty($item['has_error']) ? false : true) : null,
            $item['lock_id'] ?? null
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
