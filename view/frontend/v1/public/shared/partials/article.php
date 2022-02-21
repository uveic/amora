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
$editUrl = $article->type === ArticleType::Blog
    ? UrlBuilderUtil::buildBackofficeBlogPostUrl($responseData->getSiteLanguage(), $article->id)
    : UrlBuilderUtil::buildBackofficeArticleUrl($responseData->getSiteLanguage(), $article->id);
?>
  <article>
<?php if ($canEdit && $article->status !== ArticleStatus::Draft) { ?>
    <a class="article-edit" href="<?=$editUrl?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('shared/partials/article/article-blog-info', ['responseData' => $responseData]);
} ?>
    <?=$article->contentHtml?>
<?php if ($article->type === ArticleType::Blog) { ?>
    <p class="article-blog-footer"><?=sprintf($responseData->getLocalValue('articleBlogFooterInfo'), $email, $email)?></p>
<?php } ?>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('shared/partials/article/article-blog-bottom', ['responseData' => $responseData]);
} ?>
  </article>
