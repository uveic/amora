<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Value\Language;
use Amora\Core\Module\User\Value\UserRole;

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
