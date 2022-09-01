<?php

namespace Amora\Core\Entity\Response;

use Amora\Core\Entity\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?Pagination $pagination = null,
        public readonly ?Article $homepageContent = null,
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
