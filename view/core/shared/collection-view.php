<?php

/** @var HtmlResponseDataAbstract $responseData */

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Model\CollectionMedia;

if (!isset($collection)) {
    Core::getDefaultLogger()->logWarning('$collection has not been passed to the template builder as a parameter.');
}

/** @var Collection $collection */
// $collection is passed as a parameter to the template

if ($collection?->mainMedia) {
?>
          <img class="media-item" data-media-id="<?=$collection->mainMedia->id?>" src="<?=$collection->mainMedia->getPathWithNameMedium()?>" alt="<?=$collection->mainMedia->buildAltText()?>">
<?php } else { ?>
          <div class="no-image-simple"><?=$responseData->getLocalValue('collectionNoImage')?></div>
<?php } ?>
          <div class="block-title m-b-05 m-t-1"><?=$responseData->getLocalValue('collectionMediaTitle')?>:</div>
          <div class="collection-item-media">
<?php
    if (!$collection?->media) {
        echo '            -' . PHP_EOL;
    }

    /** @var CollectionMedia $collectionMedia */
    foreach ($collection?->media ?? [] as $collectionMedia) {
?>
            <div class="collection-media-container">
              <figure><?=$collectionMedia->media->asHtml(className: 'media-item cursor-pointer image-popup-js')?></figure>
            </div>
<?php } ?>
          </div>
