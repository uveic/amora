<?php

use Amora\App\Value\AppPageContentType;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);
?>
    <main>
      <div id="feedback" class="feedback null"></div>
      <div class="page-header">
        <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
        <span class="icon-one-line width-10-grow"><?=CoreIcons::ARTICLE?><span class="ellipsis"><?=$responseData->getLocalValue('pageContentEditTitle')?></span></span>
        <div class="links"></div>
      </div>

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
