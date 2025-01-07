<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

?>
    <section class="page-header">
      <h3><?=$responseData->getLocalValue('navAdminAlbums')?></h3>
      <div class="links">
        <a href="#" class="filter-open"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeAlbumNewUrl($responseData->siteLanguage)?>" class="button is-link header-button-new"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
