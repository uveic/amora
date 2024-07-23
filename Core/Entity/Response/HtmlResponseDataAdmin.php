<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Util\DashboardCount;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\User\Model\User;

class HtmlResponseDataAdmin extends HtmlResponseData
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Article $article = null,
        array $articles = [],
        array $articleSections = [],
        ?Album $album = null,
        array $albums = [],
        ?Pagination $pagination = null,
        public readonly ?User $user = null,
        public readonly array $users = [],
        public readonly array $files = [],
        public readonly array $emails = [],
        public readonly ?PageContent $pageContent = null,
        public readonly ?DashboardCount $dashboardCount = null,
    ) {
        parent::__construct(
            request: $request,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            pagination: $pagination,
            article: $article,
            articles: $articles,
            articleSections: $articleSections,
            albums: $albums,
            album: $album,
        );
    }
}
