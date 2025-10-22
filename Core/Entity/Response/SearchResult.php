<?php

namespace Amora\Core\Entity\Response;

use Amora\Core\Module\Article\Model\Media;

readonly class SearchResult
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $subtitle = null,
        public ?string $contentHtml = null,
        public ?Media $media = null,
        public ?string $endpoint = null,
    ) {
    }

    public function asPublicArray(string $headerTitle = ''): array
    {
        return [
            'id' => $this->id,
            'headerTitle' => $headerTitle,
            'title' => $this->title,
            'url' => $this->url,
            'subtitle' => $this->subtitle,
            'contentHtml' => $this->contentHtml,
            'media' => $this->media?->asPublicArray(),
            'endpoint' => $this->endpoint,
        ];
    }
}
