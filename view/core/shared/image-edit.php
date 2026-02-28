<?php

/** @var HtmlResponseDataAbstract $responseData */

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\CoreIcons;

/** @var Media $media */
// $media is passed as a parameter to the template

$containerId = 'generic-media-container-' . StringUtil::generateRandomString(12);

?>
          <div id="<?=$containerId?>" class="control main-image-wrapper">
            <div class="main-image-button-container">
              <a href="#" class="main-image-button main-image-button-red generic-media-delete-js<?=isset($media) ? '' : ' null'?>" data-media-id="<?=$media?->id?>" data-target-container-id="<?=$containerId?>">
                <?=CoreIcons::TRASH?>
              </a>
              <a href="#" class="main-image-button select-media-action button-media-add icon-one-line" data-type-id="<?=MediaType::Image->value?>" data-target-container-id="<?=$containerId?>" data-event-listener-action="handleGenericMainMediaClick">
                <?=CoreIcons::IMAGE . $responseData->getLocalValue('globalSelectImage')?>
              </a>
            </div>
<?php
if (isset($media)) {
    echo '            ' . $media->asHtml() . PHP_EOL;
}?>
          </div>
