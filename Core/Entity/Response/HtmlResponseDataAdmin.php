<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Entity\Request;
use Amora\Core\Entity\Util\DashboardCount;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\User\Model\User;

class HtmlResponseDataAdmin extends HtmlResponseData
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Article $article = null,
        ?array $articles = [],
        ?Pagination $pagination = null,
        public readonly ?User $user = null,
        public readonly ?array $users = [],
        public readonly ?array $files = [],
        public readonly ?array $articleSections = [],
        public readonly ?array $emails = [],
        public readonly ?array $albums = [],
        public readonly ?PageContent $pageContent = null,
        public readonly ?DashboardCount $dashboardCount = null,
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

    public function getUserToEdit(): ?User
    {
        $allUsers = $this->users;
        return $allUsers[0] ?? null;
    }
}
