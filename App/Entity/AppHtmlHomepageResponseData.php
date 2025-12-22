<?php

namespace Amora\App\Entity;

use Amora\Core\Entity\Request;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Entity\Response\Pagination;

class AppHtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?Pagination $pagination = null,
        ?Feedback $feedback = null,
        bool $isPublicPage = false,
        public readonly array $pageContentByTypeId = [],
        public readonly array $homeArticles = [],
        public readonly array $blogArticles = [],
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            feedback: $feedback,
            isPublicPage: $isPublicPage,
        );
    }
}
