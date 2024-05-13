<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$this->layout('base', ['responseData' => $responseData]);
?>
<main>
  <article class="narrow-width m-t-4 m-b-6">
    <h1 class="article-title"><?=$responseData->getLocalValue('globalPageNotFoundTitle')?></h1>
    <p class="m-b-6"><?=sprintf($responseData->getLocalValue('globalPageNotFoundContent'), UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage))?></p>
  </article>
</main>
<script type="module" src="/js/app/home-000.js"></script>