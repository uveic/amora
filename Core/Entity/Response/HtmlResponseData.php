<?php

namespace Amora\Core\Entity\Response;

use Amora\Core\Entity\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $mainImageSiteUri = null,
        ?Pagination $pagination = null,
        public readonly ?array $articles = [],
        public readonly ?Article $previousBlogPost = null,
        public readonly ?Article $nextBlogPost = null,
        public readonly ?Article $postBottomContent = null,
        public readonly ?Feedback $feedback = null,
        public readonly ?string $verificationHash = null,
        public readonly ?int $passwordUserId = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            siteImageUri: $mainImageSiteUri,
        );
    }

    public function getFirstArticle(): ?Article
    {
        return $this->articles[0] ?? null;
    }

    public function getUserName(): ?string
    {
        if (empty($this->session) || empty($this->session->user)) {
            return null;
        }

        return $this->session->user->name;
    }
}
