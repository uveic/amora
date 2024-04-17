<?php

namespace Amora\App\Router;

use Amora\App\Router\Controller\Response\AppPublicApiControllerGetSearchResultsSuccessResponse;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Service\AlbumService;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Value\QueryOrderDirection;

final class AppPublicApiController extends AppPublicApiControllerAbstract
{
    public function __construct(
        private readonly AlbumService $albumService,
    ) {
        parent::__construct();
    }

    public function authenticate(Request $request): bool
    {
        return true;
    }

    /**
     * Endpoint: /papi/search
     * Method: GET
     *
     * @param string $q Query string
     * @param string|null $isPublic Is a public page?
     * @param int|null $searchTypeId
     * @param Request $request
     * @return Response
     */
    protected function getSearchResults(string $q, ?string $isPublic, ?int $searchTypeId, Request $request): Response
    {
        $albums = $this->albumService->filterAlbumBy(
            statusIds: [
                AlbumStatus::Published->value,
                AlbumStatus::Unlisted->value,
            ],
            searchQuery: $q,
            queryOptions: new QueryOptions(
                orderBy: [
                    new QueryOrderBy('begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('word_begins_with', QueryOrderDirection::DESC),
                    new QueryOrderBy('title_contains', QueryOrderDirection::DESC),
                    new QueryOrderBy('date_start', QueryOrderDirection::DESC),
                ],
                pagination: new Response\Pagination(
                    itemsPerPage: 10,
                ),
            ),
        );

        $albumsOutput = [];
        /** @var Album $album */
        foreach ($albums as $album) {
            $albumsOutput[] = $album->asSearchResult($request->siteLanguage, $isPublic)->asPublicArray('Álbums');
        }

        return new AppPublicApiControllerGetSearchResultsSuccessResponse(
            success: true,
            results: $albumsOutput,
        );
    }
}
