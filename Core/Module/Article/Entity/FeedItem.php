<?php

namespace Amora\Core\Module\Article\Entity;

use Amora\App\Value\Language;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;

readonly class FeedItem
{
    public function __construct(
        public string $fullPath,
        public string $title,
        public string $contentHtml,
        public DateTimeImmutable $publishedOn,
        public Language $language,
        public ?User $user = null,
        public ?Media $media = null,
        public ?DateTimeImmutable $updatedAt = null,
        public array $tags = [],
    ) {}
}
