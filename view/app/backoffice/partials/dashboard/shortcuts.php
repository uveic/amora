<?php

use Amora\App\Value\AppPageContentType;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

if (!AppPageContentType::getActive()) {
    return;
}

?>
        <div class="dashboard-count">
          <h3 class="no-margin"><?=$responseData->getLocalValue('navAdminPageContentEdit')?></h3>
          <div class="page-content-container">
<?php
    /** @var AppPageContentType|PageContentType $item */
    foreach (AppPageContentType::getActive() as $item) {
        echo '            <a href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($responseData->siteLanguage, $item, $responseData->siteLanguage) . '">' . $responseData->getLocalValue(AppPageContentType::getTitleVariableName($item)) . '</a>' . PHP_EOL;
    }
?>
          </div>
        </div>
