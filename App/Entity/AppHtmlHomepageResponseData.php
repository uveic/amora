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
        ?Pagination $pagination = null,
        bool $isPublicPage = false,
        public readonly ?PageContent $pageContent = null,
        public readonly array $homeArticles = [],
        public readonly array $blogArticles = [],
        public readonly ?Feedback $feedback = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            isPublicPage: $isPublicPage,
        );
    }
}
