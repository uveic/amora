<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Util\Logger;
use Amora\Core\Module\Article\DataLayer\TagDataLayer;
use Amora\Core\Module\Article\Model\Tag;

readonly class TagService
{
    public function __construct(
        private Logger $logger,
        private TagDataLayer $tagDataLayer
    ) {
    }

    public function getTagForId(int $id): ?Tag
    {
        $res = $this->filterTagsBy(tagIds: [$id]);
        return empty($res[0]) ? null : $res[0];
    }

    public function getTagForName(string $name): ?Tag
    {
        return $this->tagDataLayer->getTagForName($name);
    }

    public function filterTagsBy(
        array $tagIds = [],
        array $articleIds = [],
        ?string $tagName = null,
    ): array {
        return $this->tagDataLayer->filterTagsBy(
            tagIds: $tagIds,
            articleIds: $articleIds,
            tagName: $tagName,
        );
    }

    public function storeTag(Tag $tag, ?int $articleId = null): ?Tag
    {
        $tag = $this->tagDataLayer->storeTag($tag);

        if ($articleId) {
            $res = $this->tagDataLayer->insertArticleTagRelation($tag->id, $articleId);

            if (empty($res)) {
                return null;
            }
        }

        return $tag;
    }

    public function destroyTag(Tag $tag): bool
    {
        $res = $this->tagDataLayer->destroyTag($tag->id);
        if (empty($res)) {
            $this->logger->logError('Error deleting tag. Tag ID: ' . $tag->id);
            return false;
        }

        return true;
    }
}
