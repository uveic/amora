<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

?>
  <section class="article-header">
    <p><a href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>">‹ <?=$responseData->siteName?></a></p>
  </section>
