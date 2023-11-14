<?php

use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

?>
  <main>
<?=$this->insert('partials/albums/filter', ['responseData' => $responseData])?>
    <section class="page-header">
      <h3><?=$responseData->getLocalValue('navAdminAlbums')?></h3>
      <div class="links">
        <a href="#" class="filter-open"><img src="/img/svg/funnel.svg" class="img-svg img-svg-25" alt="Funnel"></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeAlbumNewUrl($responseData->siteLanguage)?>" class="button is-link header-button-new"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
    <div class="backoffice-wrapper">
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
