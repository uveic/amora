<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        private ?Article $homepageContent = null,
        private array $homeArticles = [],
        private array $blogArticles = [],
        private ?UserFeedback $userFeedback = null,
        protected ?Pagination $pagination = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
        );
    }

    public function getHomepageContent(): ?Article
    {
        return $this->homepageContent;
    }

    public function getHomeArticles(): array
    {
        return $this->homeArticles;
    }

    public function getBlogArticles(): array
    {
        return $this->blogArticles;
    }

    public function getUserFeedback(): ?UserFeedback
    {
        return $this->userFeedback;
    }
}
