<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

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

$albumPublicLink = UrlBuilderUtil::buildPublicAlbumUrl(
    slug: $album->slug->slug,
    language: $album->language,
);

$publicLinkHtml = $album->status->isPublished()
    ? '<a href="' . $albumPublicLink . '">' . $albumPublicLink . '</a>'
    : $albumPublicLink;

$closeLink = UrlBuilderUtil::buildBackofficeAlbumListUrl($responseData->siteLanguage);

$this->insert('partials/albums/modal-media-caption-edit', ['responseData' => $responseData]);
$this->insert('partials/shared/modal-select-image', ['responseData' => $responseData]);

$localisationUtil = Core::getLocalisationUtil($responseData->siteLanguage);
?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h3><img src="/img/svg/images.svg" class="img-svg img-svg-25 m-r-05" width="25" height="25" alt="Edición"><?=$album->titleHtml?></h3>
      <div class="links">
        <span class="value"><a href="<?=UrlBuilderUtil::buildBackofficeAlbumEditUrl($responseData->siteLanguage, $album->id)?>"><?=$responseData->getLocalValue('globalEdit')?></a></span>
        <span class="number">#<?=$album->id?></span>
        <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="30" height="30" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      </div>
    </section>
    <section class="form-content-container">
      <section class="form-content-wrapper">
        <div class="form-two-columns-wrapper">
          <div class="flex-child m-b-15">
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalStatus')?>:</span>
              <span class="value">
<?=AlbumHtmlGenerator::generateDynamicAlbumStatusHtml($album->status, $localisationUtil, '              ')?>
              </span>
            </div>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalCreatedAt')?>:</span>
              <span class="value"><?=$createdAt?></span>
            </div>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalLanguage')?>:</span>
              <span class="value one-line"><?=$album->language->getIconFlag('m-r-05') . $album->language->getName()?></span>
            </div>
            <div class="card-info-item form-public-link">
              <span class="title"><?=$responseData->getLocalValue('albumFormPublicLinkTitle')?>:</span>
              <span class="value word-break"><?=$publicLinkHtml?></span>
            </div>
          </div>
          <div class="flex-child m-b-15">
<?php if ($album->contentHtml) { ?>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalContent')?>:</span>
              <div class="value"><?=StringUtil::nl2p($album->contentHtml) ?: '-'?></div>
            </div>
<?php } ?>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('albumFormTemplateTitle')?>:</span>
              <span class="value"><?=$album->template->name?></span>
            </div>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('editorMainImage')?>:</span>
              <div class="value album-section-main-image-container">
                <img src="<?=$album->mainMedia->getPathWithNameMedium()?>" alt="<?=$album->mainMedia->buildAltText()?>">
              </div>
            </div>
          </div>
        </div>
        <section class="album-sections-wrapper">
<?php
    /** @var AlbumSection $section */
    foreach ($album->sections as $section) {
        echo AlbumHtmlGenerator::generateAlbumSectionHtml(
            section: $section,
            localisationUtil: $localisationUtil,
            indentation: '          ',
        );
}?>
        </section>
        <a href="#" class="album-add-section-js button button-media-add" data-album-id="<?=$album->id?>">
          <img src="/img/svg/image.svg" class="img-svg img-svg-25 m-r-05" width="25" height="25" alt="<?=$responseData->getLocalValue('albumAddSection')?>">
          <span class="one-line"><?=$responseData->getLocalValue('albumAddSection')?></span>
        </a>
      </section>
    </section>
  </main>
