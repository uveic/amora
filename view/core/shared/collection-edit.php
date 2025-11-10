<?php

/** @var HtmlResponseDataAbstract $responseData */

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\CoreIcons;

/** @var Collection $collection */
// $collection is passed as a parameter to the template

$collectionIdentifier = $collection?->id ?? StringUtil::generateRandomString(10);

?>
          <div class="collection-container" data-collection-id="<?=$collection?->id?>">
            <div class="control main-image-wrapper width-100">
              <div id="collection-view-main-media" class="main-image-container main-image-container-full<?=$collection?->mainMedia ? '' : ' no-image-simple'?>" data-is-main-media="1">
<?php if ($collection?->mainMedia) { ?>
                <img class="media-item" data-media-id="<?=$collection->mainMedia->id?>" src="<?=$collection->mainMedia->getPathWithNameMedium()?>" alt="<?=$collection?->mainMedia->buildAltText()?>">
<?php } ?>
                <div class="main-image-button-container">
                  <a href="#" class="main-image-button select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="collection-view-main-media" data-event-listener-action="collectionAddMedia">
                    <?=CoreIcons::IMAGE?>
                    <span><?=$collection?->mainMedia ? $responseData->getLocalValue('globalModify') : $responseData->getLocalValue('globalSelectImage') ?></span>
                  </a>
                </div>
                <div class="collection-main-media-options<?=$collection?->mainMedia ? '' : ' null'?>">
                  <span class="media-caption collection-media-caption-js"><?=$collection?->mainMedia?->captionHtml ?: '-'?></span>
                  <span class="collection-main-media-delete-js<?=$collection?->mainMedia ? '' : ' null'?>"><?=CoreIcons::TRASH?></span>
                </div>
              </div>
            </div>
            <div>
              <div class="block-title m-t-1 m-b-05"><?=$responseData->getLocalValue('collectionMediaTitle')?>:</div>
              <div id="collection-item-media-<?=$collectionIdentifier?>" class="collection-item-media">
<?php
    /** @var CollectionMedia $collectionMediaItem */
    foreach ($collection?->media ?? [] as $collectionMediaItem) {
        echo AlbumHtmlGenerator::generateCollectionMediaHtml($collectionMediaItem, '                  ') . PHP_EOL;
    }
?>
                <a href="#" class="select-media-action button-media-add" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="collection-item-media-<?=$collectionIdentifier?>" data-event-listener-action="collectionAddMedia">
                  <?=CoreIcons::IMAGE?>
                  <span><?=$responseData->getLocalValue('globalAdd')?></span>
                </a>
              </div>
              <div class="collection-media-edit-info"><?=$responseData->getLocalValue('collectionDragAndDropToOrder')?></div>
            </div>
          </div>
