<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

?>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span><?=$responseData->getLocalValue('navAdminAlbums')?></span>
      <div class="links">
        <a href="#" class="filter-open"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeAlbumNewUrl($responseData->siteLanguage)?>" class="button is-link header-button-new"><?=CoreIcons::ADD?></a>
      </div>
    </div>
