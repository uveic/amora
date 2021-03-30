<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\Article\Model\Article;

class HtmlResponseData extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $mainImageSiteUri = null,
        protected ?array $articles = [],
        protected ?UserFeedback $userFeedback = null,
        protected ?string $verificationHash = null,
        private ?int $passwordUserId = null
    ) {
        parent::__construct($request, $pageTitle, $pageDescription, $mainImageSiteUri);
    }

    public function getArticles(): array
    {
        return $this->articles;
    }

    public function getFirstArticle(): ?Article
    {
        $allArticles = $this->getArticles();
        return $allArticles[0] ?? null;
    }

    public function getUserFeedback(): ?UserFeedback
    {
        return $this->userFeedback;
    }

    public function getPasswordUserId(): ?int
    {
        return $this->passwordUserId;
    }

    public function getUserName(): ?string
    {
        if (empty($this->getSession()) || empty($this->getSession()->getUser())) {
            return null;
        }

        return $this->getSession()->getUser()->getName();
    }

    public function getVerificationHash(): ?string
    {
        return $this->verificationHash;
    }
}
