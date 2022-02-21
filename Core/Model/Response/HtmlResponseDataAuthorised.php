<?php

namespace Amora\Core\Model\Response;

use Amora\Core\Model\Request;
use Amora\Core\Module\User\Model\User;

class HtmlResponseDataAuthorised extends HtmlResponseData
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?array $articles = [],
        ?Pagination $pagination = null,
        public readonly ?array $listOfUsers = [],
        public readonly ?array $images = [],
        public readonly ?array $articleSections = [],
    ) {
        parent::__construct(
            request: $request,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
            pagination: $pagination,
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
