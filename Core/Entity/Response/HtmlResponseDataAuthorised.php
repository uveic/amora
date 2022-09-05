<?php

namespace Amora\Core\Entity\Response;

use Amora\Core\Entity\Request;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\User\Model\User;

class HtmlResponseDataAuthorised extends HtmlResponseData
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Article $article = null,
        ?array $articles = [],
        ?Pagination $pagination = null,
        public readonly ?array $listOfUsers = [],
        public readonly ?array $files = [],
        public readonly ?array $articleSections = [],
    ) {
        parent::__construct(
            request: $request,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            pagination: $pagination,
            article: $article,
            articles: $articles,
        );
    }

    public function getUser(): ?User
    {
        if (empty($this->session)) {
            return null;
        }

        return $this->session->user;
    }

    public function getUserName(): string
    {
        return $this->getUser() ? $this->getUser()->getNameOrEmail() : '';
    }

    public function getUserToEdit(): ?User
    {
        $allUsers = $this->listOfUsers;
        return $allUsers[0] ?? null;
    }
}
