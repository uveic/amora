<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

?>
  <section>
    <p class="m-l-1"><a href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>">‹ <?=$responseData->siteName?></a></p>
  </section>
