<?php

use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$closeLink = UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage);

$this->layout('base', ['responseData' => $responseData]);
?>
    <main>
      <div id="feedback" class="feedback null"></div>
      <section class="page-header">
        <span><?=$responseData->getLocalValue('pageContentEditTitle')?></span>
        <div class="links">
          <a href="<?=$closeLink?>"><?=CoreIcons::CLOSE?></a>
        </div>
      </section>

      <div class="backoffice-wrapper">
        <div class="page-content-container">
<?php
    /** @var AppPageContentType|PageContentType $item */
    foreach (AppPageContentType::getActive() as $item) {
        echo '          <a href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($responseData->siteLanguage, $item, $responseData->siteLanguage) . '">' . $responseData->getLocalValue(AppPageContentType::getTitleVariableName($item)) . '</a>' . PHP_EOL;
     }
?>
        </div>
      </div>
    </main>
