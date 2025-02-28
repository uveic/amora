<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$this->insert('partials/images/modal-image', ['responseData' => $responseData]);

$count = 0;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <section class="page-header">
      <span><?=$responseData->getLocalValue('navAdminImages')?></span>
      <div class="links">
        <div>
          <input class="null" type="file" id="images" name="images" multiple="" accept="image/png, image/jpeg, image/webp">
          <label for="images" class="link-add"><?=CoreIcons::ADD?></label>
        </div>
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
    <div class="content-images">
      <div id="images-list">
<?php
    /** @var Media $image */
    foreach ($responseData->media as $image) {
        $count++;
?>
        <figure class="image-container">
          <?=$image->asHtml(size: ImageSize::Small, lazyLoading: $count > 10) . PHP_EOL?>
        </figure>
<?php } ?>
      </div>
      <a href="#" class="media-load-more media-load-more-js<?=$count >= 50 ? '' : ' null'?>" data-type-id="<?=MediaType::Image->value?>" data-direction="<?=QueryOrderDirection::DESC->name?>" data-event-listener-action="displayNextImagePopup">
        <span><?=$responseData->getLocalValue('globalMore')?></span>
      </a>
    </div>
  </main>
