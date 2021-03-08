<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$articleEditUrl = $responseData->getBaseUrlWithLanguage() . 'backoffice/articles/' .
    ($responseData->getFirstArticle()
        ? $responseData->getFirstArticle()->getId()
        : 'new?type=homepage'
);

?>
        <div class="content-flex-block width-45-percent">
          <h2><?=$responseData->getLocalValue('dashboardHomepage')?></h2>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('dashboardHomepageEdit')?>"><a href="<?=$articleEditUrl?>"><?=$responseData->getLocalValue('dashboardHomepageEdit')?></a></p>
        </div>
