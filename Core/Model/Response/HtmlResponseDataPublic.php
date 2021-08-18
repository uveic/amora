<?php

namespace Amora\Core\Model\Response;

use Amora\App\Module\Budget\Model\Budget;
use Amora\App\Module\Budget\Model\BudgetOwner;
use Amora\Core\Model\Request;

class HtmlResponseDataPublic extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        private array $meters = [],
        private ?BudgetOwner $budgetOwner = null,
        private ?Budget $budget = null,
        private array $budgets = [],
        private array $budgetItems = [],
    ) {
        parent::__construct(
            request: $request,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
        );
    }

    public function getMeters(): array
    {
        return $this->meters;
    }

    public function getBudgetOwner(): ?BudgetOwner
    {
        return $this->budgetOwner;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function getBudgets(): array
    {
        return $this->budgets;
    }

    public function getBudgetItems(): array
    {
        return $this->budgetItems;
    }
}
