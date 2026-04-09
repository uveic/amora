<?php

namespace Amora\App\Entity;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Entity\Response\Pagination;

class AppHtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Pagination $pagination = null,
        ?Feedback $feedback = null,
        bool $isPublicPage = false,
        public readonly array $pageContentByTypeId = [],
        public readonly array $homeArticles = [],
        public readonly array $blogArticles = [],
        public readonly ?PageContent $pageContent = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            feedback: $feedback,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            isPublicPage: $isPublicPage,
        );
    }
}
