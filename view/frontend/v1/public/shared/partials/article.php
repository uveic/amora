<?php

use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();
$canEdit = $responseData->getSession() && $responseData->getSession()->isAdmin();
$preview = $responseData->getRequest()->getGetParam('preview');

if ($article) {
?>
  <article>
<?php if ($canEdit && !$preview) { ?>
    <a class="article-edit" href="<?=UrlBuilderUtil::getBackofficeArticleUrl($responseData->getSiteLanguage(), $article->getId())?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
    <?=$article->getContentHtml()?>
<?php if ($article->getTypeId() === ArticleType::BLOG) {
    $this->insert('shared/partials/article/article-info', ['responseData' => $responseData]);
} ?>
  </article>
<?php
}
?>
