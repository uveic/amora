<?php

namespace uve\core\model\response;

use uve\core\model\Request;
use uve\core\module\article\model\Article;
use uve\core\module\user\model\Session;

class HtmlResponseData extends HtmlResponseDataAbstract
{
    protected array $articles;
    protected ?UserFeedback $userFeedback;
    protected ?Session $session;
    protected ?string $verificationHash;
    private ?int $forgotPasswordUserId;

    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $mainImageSiteUri = null,
        ?array $articles = [],
        ?UserFeedback $userFeedback = null,
        ?string $verificationHash = null,
        ?int $forgotPasswordUserId = null
    ) {
        parent::__construct($request, $pageTitle, $pageDescription, $mainImageSiteUri);

        $this->articles = $articles ?? [];
        $this->userFeedback = $userFeedback;
        $this->session = $request->getSession();
        $this->verificationHash = $verificationHash;
        $this->forgotPasswordUserId = $forgotPasswordUserId;
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
