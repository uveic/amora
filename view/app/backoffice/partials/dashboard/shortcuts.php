<?php

use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$groupOfLinks = [];

foreach (AppPageContentType::getActive() as $item) {
    foreach (Language::getActive() as $language) {
        $groupOfLinks[AppPageContentType::getTitleVariableName($item)][] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($responseData->siteLanguage, $item, $language) . '">' . $language->getIconFlag('img-svg-25') . '</a>';
    }
}
?>
        <div class="dashboard-count">
          <h3 class="no-margin"><?=$responseData->getLocalValue('navAdminPageContentEdit')?></h3>
          <div class="dashboard-cards-wrapper">
<?php foreach ($groupOfLinks as $titleVariableName => $links) { ?>
            <div>
              <span><?=$responseData->getLocalValue($titleVariableName)?>:</span>
              <span><?=implode('', $links)?></span>
            </div>
<?php } ?>
          </div>
        </div>
