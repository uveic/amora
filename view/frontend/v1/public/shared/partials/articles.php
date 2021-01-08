<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\util\DateUtil;

/** @var HtmlResponseData $responseData */

?>
<section class="content-medium-width m-t-2 m-b-2">
  <h1>Blog</h1>
<?php
foreach ($responseData->getArticles() as $article) {
?>
  <h2>
    <a class="black" href="<?=$this->e($responseData->getBaseUrl() . $article->getUri())?>" class="article-title"><?=$this->e($article->getTitle())?></a>
    <span class="article-info"><?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, false))?></span>
  </h2>
<?php
}
?>
</section>
