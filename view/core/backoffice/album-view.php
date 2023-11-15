<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

if (empty($responseData->album)) {
    return;
}

$this->layout('base', ['responseData' => $responseData]);

$album = $responseData->album;

$createdAt = DateUtil::formatDate(
    date: $album->createdAt,
    lang: $responseData->siteLanguage,
    includeTime: true,
);

$now = new DateTimeImmutable();
$albumPublicLink = UrlBuilderUtil::buildPublicAlbumUrl(
    slug: $album->slug->slug,
    language: $responseData->siteLanguage,
);

$publicLinkHtml = $album->status->isPublic()
    ? '<a href="' . $albumPublicLink . '">' . $albumPublicLink . '</a>'
    : $albumPublicLink;

$closeLink = UrlBuilderUtil::buildBackofficeAlbumListUrl($responseData->siteLanguage);

$this->insert('partials/albums/modal-add-section', ['responseData' => $responseData]);
$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h3><img src="/img/svg/images.svg" class="img-svg img-svg-25 m-r-05" width="25" height="25" alt="Edición"><?=$album->titleHtml?></h3>
      <div class="links">
        <span class="number">#<?=$album->id?></span>
        <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="30" height="30" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      </div>
    </section>
    <section class="form-content-container">
      <section class="form-content-wrapper">
        <section class="flex-child m-b-15">
          <div class="card-info-item">
            <span class="title">Estado:</span>
            <span class="value">
<?= AlbumHtmlGenerator::generateDynamicAlbumStatusHtml($album->status, '              ')?>
            </span>
          </div>
          <div class="card-info-item">
            <span class="title">Creado o:</span>
            <span class="value"><?=$createdAt?></span>
          </div>
          <div class="card-info-item form-public-link">
            <span class="title">Enderezo público:</span>
            <span class="value word-break"><?=$publicLinkHtml?></span>
          </div>
          <div class="card-info-item">
            <span class="title">Deseño do álbum:</span>
            <span class="value"><?=$album->template->name?></span>
          </div>
          <div class="card-info-item">
            <span class="title"><?=$responseData->getLocalValue('globalLanguage')?>:</span>
            <span class="value one-line"><?=$album->language->getIconFlag('m-r-05') . $album->language->getName()?></span>
          </div>
        </section>
        <section class="album-sections-wrapper">
<?php
    /** @var AlbumSection $section */
    foreach ($album->sections as $section) {
        echo AlbumHtmlGenerator::generateAlbumSectionHtml(
            section: $section,
            indentation: '          ',
        );
}?>
        </section>
        <a href="#" class="album-add-section-js button is-standard" data-album-id="<?=$album->id?>">
          <img src="/img/svg/image-white.svg" class="img-svg img-svg-30" width="20" height="20" alt="Nova sección">
          <span class="one-line">Nova sección</span>
        </a>
      </section>
      <section class="flex-child activity-wrapper">
        <a class="form-edit" href="<?=UrlBuilderUtil::buildBackofficeAlbumEditUrl($responseData->siteLanguage, $album->id)?>"><?=$responseData->getLocalValue('globalEdit')?></a>
        <div class="album-title"><?=$album->titleHtml?></div>
        <div class="album-content"><?=StringUtil::nl2p($album->contentHtml)?></div>
        <div class="card-info-item album-image">
          <span class="title"><?=$responseData->getLocalValue('editorMainImage')?>:</span>
          <div class="main-image-container main-image-container-full">
            <img src="<?=$album->mainMedia->getPathWithNameMedium()?>" alt="<?=$album->mainMedia->buildAltText()?>">
          </div>
        </div>
      </section>
    </section>
  </main>
