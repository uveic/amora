<?php

namespace Amora\Core\Module\Article\Model;

class Image
{
    private ?int $id;
    private int $userId;
    private string $fullUrlOriginal;
    private ?string $fullUrlMedium;
    private ?string $fullUrlBig;
    private string $filePathOriginal;
    private ?string $filePathMedium;
    private ?string $filePathBig;
    private ?string $caption;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        ?int $id,
        int $userId,
        string $fullUrlOriginal,
        ?string $fullUrlMedium,
        ?string $fullUrlBig,
        string $filePathOriginal,
        ?string $filePathMedium,
        ?string $filePathBig,
        ?string $caption,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->fullUrlOriginal = $fullUrlOriginal;
        $this->fullUrlMedium = $fullUrlMedium;
        $this->fullUrlBig = $fullUrlBig;
        $this->filePathOriginal = $filePathOriginal;
        $this->filePathMedium = $filePathMedium;
        $this->filePathBig = $filePathBig;
        $this->caption = $caption;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $image): Image
    {
        $id = isset($image['image_id'])
            ? (int)$image['image_id']
            : (empty($image['id']) ? null : (int)$image['id']);

        $userId = isset($image['image_user_id'])
            ? (int)$image['image_user_id']
            : (int)$image['user_id'];

        $createdAt = empty($image['image_created_at'])
            ? $image['created_at']
            : $image['image_created_at'];

        $updatedAt = empty($image['image_updated_at'])
            ? $image['updated_at']
            : $image['image_updated_at'];

        return new Image(
            $id,
            $userId,
            $image['full_url_original'],
            $image['full_url_medium'] ?? null,
            $image['full_url_big'] ?? null,
            $image['file_path_original'],
            $image['file_path_medium'] ?? null,
            $image['file_path_big'] ?? null,
            empty($image['caption']) ? null : $image['caption'],
            $createdAt,
            $updatedAt
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'full_url_original' => $this->getFullUrlOriginal(),
            'full_url_medium' => $this->getFullUrlMedium(),
            'full_url_big' => $this->getFullUrlLarge(),
            'file_path_original' => $this->getFilePathOriginal(),
            'file_path_medium' => $this->getFilePathMedium(),
            'file_path_big' => $this->getFilePathBig(),
            'caption' => $this->getCaption(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFullUrlOriginal(): string
    {
        return $this->fullUrlOriginal;
    }

    public function getFullUrlMedium(): ?string
    {
        return $this->fullUrlMedium ?? ($this->getFullUrlLarge() ?? $this->getFullUrlOriginal());
    }

    public function getFullUrlLarge(): ?string
    {
        return $this->fullUrlBig ?? $this->getFullUrlOriginal();
    }

    public function getFilePathOriginal(): string
    {
        return $this->filePathOriginal;
    }

    public function getFilePathMedium(): ?string
    {
        return $this->filePathMedium;
    }

    public function getFilePathBig(): ?string
    {
        return $this->filePathBig;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
