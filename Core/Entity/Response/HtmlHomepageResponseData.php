<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Entity\Request;

class HtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?Pagination $pagination = null,
        public readonly ?PageContent $pageContent = null,
        public readonly array $homeArticles = [],
        public readonly array $blogArticles = [],
        public readonly ?Feedback $feedback = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
        );
    }
}
