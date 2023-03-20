<?php

namespace Amora\Core\Entity\Response;

use Amora\App\Module\Analytics\Entity\ReportViewCount;
use Amora\Core\Entity\Request;

class HtmlResponseDataAnalytics extends HtmlResponseDataAbstract
{
    public function __construct(
        Request $request,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?Pagination $pagination = null,
        public readonly ?ReportViewCount $reportPageViews = null,
        public readonly ?ReportViewCount $reportVisitors = null,
        public readonly array $visitors = [],
        public readonly array $pages = [],
        public readonly array $countries = [],
        public readonly array $sources = [],
        public readonly array $devices = [],
        public readonly array $browsers = [],
        public readonly array $languages = [],
    ) {
        parent::__construct(
            request: $request,
            pagination: $pagination,
            pageTitle: $pageTitle,
            pageDescription: $pageDescription,
        );
    }
}
