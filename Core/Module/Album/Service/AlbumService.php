<?php

namespace Amora\Core\Module\Album\Service;

use Amora\App\Value\Language;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Album\Datalayer\AlbumDataLayer;
use Amora\Core\Module\Album\Model\Album;

readonly class AlbumService
{
    public function __construct(
        private AlbumDataLayer $albumDataLayer,
    ) {}

    public function getAlbumForId(
        int $id,
        ?Language $language = null,
    ): ?Album {
        $res = $this->filterAlbumBy(
            albumIds: [$id],
            languageIsoCodes: $language ? [$language->value] : [],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function filterAlbumBy(
        array $albumIds = [],
        array $languageIsoCodes = [],
        array $statusIds = [],
        array $templateIds = [],
        ?string $path = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->albumDataLayer->filterAlbumBy(
            albumIds: $albumIds,
            languageIsoCodes: $languageIsoCodes,
            statusIds: $statusIds,
            templateIds: $templateIds,
            path: $path,
            queryOptions: $queryOptions,
        );
    }

    public function storeAlbum(Album $item): Album
    {
        return $this->albumDataLayer->storeAlbum($item);
    }

    public function getTotalAlbums(): int {
        return $this->albumDataLayer->getTotalAlbums();
    }
}
