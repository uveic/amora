<?php

namespace uve\core\model\response;

use uve\core\model\Request;
use uve\core\module\user\model\User;
use uve\core\module\article\value\ArticleStatus;
use uve\core\module\article\value\ArticleType;
use uve\core\value\Language;
use uve\core\module\user\value\UserRole;

class HtmlResponseDataAuthorised extends HtmlResponseData
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?array $articles = [],
        protected ?array $usersList = [],
        protected ?array $images = [],
        protected ?array $articleSections = [],
    ) {
        $this->usersList = $usersList ?? [];

        parent::__construct(
            $request,
            $pageTitle,
            $pageDescription,
            null,
            $articles,
        );
    }

    public function getUser(): ?User
    {
        if (empty($this->session)) {
            return null;
        }

        return $this->session->getUser();
    }

    public function getUserName(): string
    {
        return $this->getUser() ? $this->getUser()->getNameOrEmail() : '';
    }

    public function getListOfUsers(): array
    {
        return $this->usersList ?? [];
    }

    public function getImages(): array
    {
        return $this->images ?? [];
    }

    public function getArticleSections(): array
    {
        return $this->articleSections ?? [];
    }

    public function getUserToEdit(): ?User
    {
        $allUsers = $this->getListOfUsers();
        return $allUsers[0] ?? null;
    }

    public function getArticleStatuses(): array
    {
        return ArticleStatus::getAll();
    }

    public function getArticleTypes(): array
    {
        return ArticleType::getAll();
    }

    public function getUserRoles(): array
    {
        return UserRole::getAll();
    }

    public function getLanguages(): array
    {
        return Language::getAll();
    }
}
