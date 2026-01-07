<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Util\DashboardCount;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Value\PageContentType;
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
        public readonly array $sessions = [],
        public readonly array $media = [],
        public readonly array $emails = [],
        public readonly array $pageContentAll = [],
        public readonly ?PageContent $pageContent = null,
        public readonly PageContentType|AppPageContentType|null $pageContentType = null,
        public readonly ?Language $pageContentLanguage = null,
        public readonly ?int $pageContentSequence = null,
        public readonly ?DashboardCount $dashboardCount = null,
        public readonly ?int $mediaLastPage = null,
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
