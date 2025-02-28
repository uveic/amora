<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;
use Amora\Core\Value\QueryOrderDirection;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$displayLoadMore = count($responseData->media) >= 50;

?>
  <div id="feedback" class="feedback null"></div>
  <main>
    <section class="page-header">
      <span><?=$responseData->getLocalValue('navAdminMedia')?></span>
      <div class="links">
        <div>
          <input class="null" type="file" id="media" name="media" multiple="" accept="*">
          <label for="media" class="link-add"><?=CoreIcons::ADD?></label>
        </div>
        <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>

    <div class="backoffice-wrapper">
      <div id="media-container">
<?php
    /** @var Media $media */
    foreach ($responseData->media as $media) {
?>
        <div class="file-container">
          <?=$media->asHtml()?>
        </div>
<?php } ?>
      </div>
      <a href="#" class="media-load-more media-load-more-js<?=$displayLoadMore ? '' : ' null'?>" data-type-id="" data-direction="<?=QueryOrderDirection::DESC->name?>" data-event-listener-action="displayNextImagePopup">
        <span><?=$responseData->getLocalValue('globalMore')?></span>
      </a>
    </div>
  </main>
