<?php

namespace uve\core\module\article\service;

use uve\core\Logger;
use uve\core\module\article\datalayer\ArticleDataLayer;
use uve\core\module\article\model\Article;
use uve\core\module\article\value\ArticleStatus;

class ArticleService
{
    private Logger $logger;
    private ArticleDataLayer $articleDataLayer;
    private ImageService $imageService;

    public function __construct(
        Logger $logger,
        ArticleDataLayer $articleDataLayer,
        ImageService $imageService
    ) {
        $this->logger = $logger;
        $this->articleDataLayer = $articleDataLayer;
        $this->imageService = $imageService;
    }

    public function getArticleForId(int $id): ?Article
    {
        return $this->articleDataLayer->getArticleForId($id);
    }

    public function getArticleForUri(string $uri): ?Article
    {
        return $this->articleDataLayer->getArticleForUri($uri);
    }

    public function getAllArticles(): array
    {
        return $this->articleDataLayer->getAllArticles();
    }

    public function updateArticle(Article $article, ?string $userIp, ?string $userAgent): Article
    {
        return $this->articleDataLayer->updateArticle($article, $userIp, $userAgent);
    }

    public function deleteArticle(Article $article, ?string $userIp, ?string $userAgent): bool
    {
        return $this->articleDataLayer->deleteArticle($article, $userIp, $userAgent);
    }

    public function createNewArticle(
        Article $article,
        ?string $userIp,
        ?string $userAgent
    ): Article {
        return $this->articleDataLayer->createNewArticle($article, $userIp, $userAgent);
    }

    public function getArticlesForHome(): array
    {
        return $this->articleDataLayer->getArticlesWithStatus(ArticleStatus::PUBLISHED);
    }
}
