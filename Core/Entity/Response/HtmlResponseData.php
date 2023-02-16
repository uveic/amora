<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Entity\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $siteImagePath = null,
        ?Pagination $pagination = null,
        public readonly ?Article $article = null,
        public readonly ?array $articles = [],
        public readonly ?Article $previousBlogPost = null,
        public readonly ?Article $nextBlogPost = null,
        public readonly ?PageContent $postBottomContent = null,
        public readonly ?Feedback $feedback = null,
        public readonly ?string $verificationHash = null,
        public readonly ?int $passwordUserId = null,
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            siteImagePath: $siteImagePath,
        );
    }
}
