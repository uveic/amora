<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\User\Model\Session;

class HtmlResponseData extends HtmlResponseDataAbstract
{
    protected ?Session $session;

    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $mainImageSiteUri = null,
        protected ?array $articles = [],
        protected ?UserFeedback $userFeedback = null,
        protected ?string $verificationHash = null,
        private ?int $forgotPasswordUserId = null
    ) {
        parent::__construct($request, $pageTitle, $pageDescription, $mainImageSiteUri);

        $this->session = $request->getSession();
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

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function getForgotPasswordUserId(): ?int
    {
        return $this->forgotPasswordUserId;
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

    public function isUserVerified(): bool
    {
        if (empty($this->getSession())) {
            return false;
        }
        return $this->getSession()->getUser()->isVerified();
    }

    public function minutesSinceUserRegistration(): int
    {
        if (empty($this->getSession())) {
            return 0;
        }

        return round((time() - strtotime($this->getSession()->getUser()->getCreatedAt())) / 60);
    }
}
