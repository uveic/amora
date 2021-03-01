<?php

namespace Amora\Core\Module\Mailer\Model;

class MailerLogItem
{
    private ?int $id;
    private int $mailerQueueId;
    private string $createdAt;
    private string $request;
    private ?string $response;
    private ?bool $sent;
    private ?string $errorMessage;

    public function __construct(
        ?int $id,
        int $mailerQueueId,
        string $createdAt,
        string $request,
        ?string $response = null,
        ?bool $sent = null,
        ?string $errorMessage = null
    ) {
        $this->id = $id;
        $this->mailerQueueId = $mailerQueueId;
        $this->createdAt = $createdAt;
        $this->request = $request;
        $this->response = $response;
        $this->sent = $sent;
        $this->errorMessage = $errorMessage;
    }

    public static function fromArray(array $item): MailerLogItem
    {
        $id = empty($item['id'])
            ? (empty($item['mail_id']) ? null : $item['mail_id'])
            : (int)$item['id'];

        return new MailerLogItem(
            $id,
            $item['mailer_queue_id'],
            $item['created_at'],
            $item['request'],
            $item['response'] ?? null,
            isset($item['sent']) ? (empty($item['sent']) ? false : true) : null,
            $item['error_message'] ?? null
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'mailer_queue_id' => $this->getMailerQueueId(),
            'created_at' => $this->getCreatedAt(),
            'request' => $this->getRequest(),
            'response' => $this->getResponse(),
            'sent' => $this->getSent(),
            'error_message' => $this->getErrorMessage(),
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

    public function getMailerQueueId(): int
    {
        return $this->mailerQueueId;
    }

    public function getRequest(): string
    {
        return $this->request;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function getSent(): ?bool
    {
        return $this->sent;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
