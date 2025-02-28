<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

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
      <h3><?=CoreIcons::IMAGES . $album->titleHtml?></h3>
      <div class="links">
        <span class="value"><a href="<?=UrlBuilderUtil::buildBackofficeAlbumEditUrl($responseData->siteLanguage, $album->id)?>"><?=$responseData->getLocalValue('globalEdit')?></a></span>
        <span class="number">#<?=$album->id?></span>
        <a href="<?=$closeLink?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
    <div class="form-content-container">
      <div class="form-content-wrapper">
        <div class="form-two-columns-wrapper">
          <div class="flex-child m-b-15">
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalStatus')?>:</span>
              <div class="value">
<?=AlbumHtmlGenerator::generateDynamicAlbumStatusHtml($album->status, $localisationUtil, '              ')?>
              </div>
            </div>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalCreatedAt')?>:</span>
              <span class="value"><?=$createdAt?></span>
            </div>
            <div class="card-info-item">
              <span class="title"><?=$responseData->getLocalValue('globalLanguage')?>:</span>
              <span class="value text-one-line"><?=$album->language->getIconFlag('m-r-05') . $album->language->getName()?></span>
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
              <div class="value collection-main-image-container">
                <img src="<?=$album->mainMedia->getPathWithNameMedium()?>" alt="<?=$album->mainMedia->buildAltText()?>">
              </div>
            </div>
          </div>
        </div>
        <section class="collections-wrapper">
<?php
    /** @var Collection $collection */
    foreach ($album->collections as $collection) {
        echo AlbumHtmlGenerator::generateCollectionHtml(
            collection: $collection,
            localisationUtil: $localisationUtil,
            indentation: '          ',
        );
}?>
        </section>
        <a href="#" class="album-add-collection-js button button-media-add" data-album-id="<?=$album->id?>">
          <?=CoreIcons::IMAGE?>
          <span class="text-one-line"><?=$responseData->getLocalValue('albumAddCollection')?></span>
        </a>
      </div>
    </div>
  </main>
