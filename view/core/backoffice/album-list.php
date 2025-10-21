<?php

use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

?>
  <main>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span class="icon-one-line width-10-grow"><?=CoreIcons::IMAGES?><span class="ellipsis"><?=$responseData->getLocalValue('navAdminAlbums')?></span></span>
      <div class="links">
        <a href="#" class="filter-open"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeAlbumNewUrl($responseData->siteLanguage)?>"><?=CoreIcons::ADD?></a>
      </div>
    </div>
    <div class="backoffice-wrapper">
<?=$this->insert('partials/albums/filter', ['responseData' => $responseData])?>
      <div class="album-wrapper">
<?php
    /** @var Album $album */
    foreach ($responseData->albums as $album) {
        echo AlbumHtmlGenerator::generateAlbumRowHtml(
            responseData: $responseData,
            album: $album,
            indentation: '        ',
        );
    }
?>
      </div>
    </div>
  </main>
