<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;

/** @var HtmlHomepageResponseData $responseData */

?>
<section class="home-main">
  <?=$responseData->getArticle() ? $responseData->getArticle()->getContentHtml() : '';?>
</section>
