<?php

use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */
$article = $responseData->getFirstArticle();

if ($article === null) {
    return;
}

$canEdit = $responseData->getSession() && $responseData->getSession()->isAdmin();

$email = Core::getConfigValue('siteAdminEmail');
$editUrl = $article->getTypeId() === ArticleType::BLOG
    ? UrlBuilderUtil::getBackofficeBlogPostUrl($responseData->getSiteLanguage(), $article->getId())
    : UrlBuilderUtil::getBackofficeArticleUrl($responseData->getSiteLanguage(), $article->getId());
?>
  <article>
<?php if ($canEdit && $article->getStatusId() !== ArticleStatus::DRAFT->value) { ?>
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
