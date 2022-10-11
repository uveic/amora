<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Analytics\Entity\ReportPageView;
use Amora\Core\Entity\Request;

class HtmlResponseDataAnalytics extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Pagination $pagination = null,
        public readonly ?ReportPageView $reportPageViews = null,
        public readonly array $pages = [],
        public readonly array $countries = [],
        public readonly array $sources = [],
        public readonly array $devices = [],
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
        );
    }
}
