<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;

/** @var HtmlHomepageResponseData $responseData */

?>
<section class="home-main">
  <?=$responseData->homepageContent ? $responseData->homepageContent->contentHtml : '';?>
</section>
