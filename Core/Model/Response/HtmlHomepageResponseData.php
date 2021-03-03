<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlHomepageResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        private ?Article $article = null,
        private array $tagArticles = [],
        private array $blogArticles = [],
    ) {
        parent::__construct($request);
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function getTagArticles(): array
    {
        return $this->tagArticles;
    }

    public function getBlogArticles(): array
    {
        return $this->blogArticles;
    }
}
