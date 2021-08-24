<?php

use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();
$canEdit = $responseData->getSession() && $responseData->getSession()->isAdmin();
$preview = $responseData->getRequest()->getGetParam('preview');

$email = Core::getConfigValue('siteAdminEmail');

if ($article) {
    $editUrl = $article->getTypeId() === ArticleType::BLOG
        ? UrlBuilderUtil::getBackofficeBlogPostUrl($responseData->getSiteLanguage(), $article->getId())
        : UrlBuilderUtil::getBackofficeArticleUrl($responseData->getSiteLanguage(), $article->getId());

?>
  <article>
<?php if ($canEdit && !$preview) { ?>
    <a class="article-edit" href="<?=$editUrl?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
<?php if ($article->getTypeId() === ArticleType::BLOG) {
    $this->insert('shared/partials/article/article-blog-info', ['responseData' => $responseData]);
} ?>
    <?=$article->getContentHtml()?>
<?php if ($article->getTypeId() === ArticleType::BLOG) { ?>
    <p class="article-blog-footer"><?=sprintf($responseData->getLocalValue('articleBlogFooterInfo'), $email, $email)?></p>
<?php } ?>
  </article>
<?php
}
?>
