<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$displayLoadMore = count($responseData->files) >= 50;

?>
  <div id="feedback" class="feedback null"></div>
  <main class="backoffice-wrapper">
    <div class="field m-t-0 m-b-0">
      <div id="upload-media">
        <div id="upload-media-info">
          <h1><?=$responseData->getLocalValue('navAdminMedia')?></h1>
        </div>
        <div id="upload-media-control">
          <input class="null" type="file" id="media" name="media" multiple="" accept="*">
          <label for="media" class="input-file-label button-dark">
            <img class="img-svg img-svg-25 m-r-05" width="20" height="20" src="/img/svg/files.svg" alt="<?=$responseData->getLocalValue('navAdminMedia')?>'">
            <span><?=$responseData->getLocalValue('globalUploadMedia')?></span>
          </label>
        </div>
      </div>
    </div>
    <div id="media-container">
<?php
    /** @var Media $media */
    foreach ($responseData->files as $media) {
        $dateString = DateUtil::formatDate(
            date: $media->createdAt,
            lang: $responseData->siteLanguage,
            includeTime: true,
        );
?>
      <a href="<?=$media->getPathWithNameMedium()?>" target="_blank" class="media-item" data-media-id="<?=$media->id?>">
        <span class="media-id">#<?=$media->id?></span>
        <?=MediaType::getIcon($media->type, 'img-svg-40 m-r-05')?>
        <span class="media-name"><?=$media->buildAltText() ?: $media->filenameOriginal?></span>
        <span class="media-info"><?=sprintf($responseData->getLocalValue('mediaUploadedBy'), $media->user ? $media->user->name : '???', $dateString)?>.</span>
      </a>
<?php } ?>
    </div>
    <a href="#" class="media-load-more media-load-more-js<?=$displayLoadMore ? '' : ' null'?>" data-type-id="" data-direction="<?=QueryOrderDirection::DESC->name?>" data-event-listener-action="displayNextImagePopup">
      <span><?=$responseData->getLocalValue('globalMore')?></span>
    </a>
  </main>
