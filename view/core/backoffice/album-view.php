<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

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

$publicLinkClass = $album->status->isPublic() ? '' : ' null';

?>
  <main>
    <input type="hidden" name="albumId" value="<?=$album->id?>">
    <div id="feedback" class="feedback null"></div>
    <section class="form-content-container">
      <section class="form-content-wrapper">
        <section class="flex-child m-b-15">
          <div class="block-title">
            <h2><img src="/img/svg/file-text.svg" class="img-svg img-svg-25 m-r-05" width="25" height="25" alt="Edición"><?=$album->titleHtml?></h2>
            <span class="number">#<?=$album->id?></span>
          </div>
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
          <div class="card-info-item form-public-link<?=$publicLinkClass?>">
            <span class="title">Enderezo público:</span>
            <span class="value word-break">
              <a class="slug-slug" data-slug="<?=$album->slug->slug?>" href="<?=$albumPublicLink?>"><?=$albumPublicLink?></a>
            </span>
          </div>
        </section>
        <section class="flex-child">
          <a class="form-edit" href="<?=UrlBuilderUtil::buildBackofficeAlbumEditUrl($responseData->siteLanguage, $album->id)?>"><?=$responseData->getLocalValue('globalEdit')?></a>
          <h2 class="content-title"><?=$album->titleHtml?></h2>
          <div class="m-t-2"><?=StringUtil::nl2p($album->contentHtml)?></div>
        </section>
      </section>
      <section class="flex-child activity-wrapper">
        <div class="card-info-item main-image-wrapper">
          <span class="title">Imaxe destacada:</span>
          <div class="main-image-container main-image-container-full">
            <img src="<?=$album->mainMedia->getPathWithNameMedium()?>" alt="<?=$album->mainMedia->buildAltText()?>">
          </div>
        </div>
      </section>
    </section>
  </main>
