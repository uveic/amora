<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Logger;
use Amora\Core\Module\Article\Datalayer\TagDataLayer;
use Amora\Core\Module\Article\Model\Tag;

class TagService
{
    public function __construct(
        private Logger $logger,
        private TagDataLayer $tagDataLayer
    ) {}

    public function getTagForId(int $id): ?Tag
    {
        return $this->tagDataLayer->getTagForId($id);
    }

    public function getTagForName(string $name): ?Tag
    {
        return $this->tagDataLayer->getTagForName($name);
    }

    public function getAllTags(bool $asPlainArray = false): array
    {
        $tags = $this->tagDataLayer->filterTagsBy();

        if ($asPlainArray) {
            $output = [];
            /** @var Tag $tag */
            foreach ($tags as $tag) {
                $output[] = $tag->asArray();
            }
            return $output;
        }

        return $tags;
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
