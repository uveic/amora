<?php

use Amora\App\Value\AppPageContentType;
use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

$closeLink = UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage);

$groupOfLinks = [];

/** @var AppPageContentType|PageContentType $item */
foreach (AppPageContentType::getActive() as $item) {
    /** @var Language $language */
    foreach (Core::getAllLanguages() as $language) {
        $groupOfLinks[AppPageContentType::getTitleVariableName($item)][] = '<a class="m-l-1" href="' . UrlBuilderUtil::buildBackofficeContentEditUrl($responseData->siteLanguage, $item, $language) . '">' . $language->getIconFlag('img-svg-25') . '</a>';
    }
}

$this->layout('base', ['responseData' => $responseData]);
?>
    <main>
      <div id="feedback" class="feedback null"></div>
      <section class="page-header">
        <h3><?=$responseData->getLocalValue('pageContentEditTitle')?></h3>
        <div class="links">
          <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="Volver"></a>
        </div>
      </section>

      <div class="backoffice-wrapper">
        <div>
          <div class="dashboard-cards-wrapper m-t-2">
<?php foreach ($groupOfLinks as $titleVariableName => $links) { ?>
            <div>
              <span><?=$responseData->getLocalValue($titleVariableName)?>:</span>
              <span><?=implode('', $links)?></span>
            </div>
<?php } ?>
          </div>
        </div>
      </div>
    </main>
