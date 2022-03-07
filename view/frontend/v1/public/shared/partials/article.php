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

$canEdit = $responseData->request->session && $responseData->request->session->isAdmin();
$postBottomContent = $responseData->postBottomContent ? $responseData->postBottomContent->contentHtml : '';

$email = Core::getConfig()->siteAdminEmail;
$editUrl = UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $article->id);
?>
  <article>
<?php if ($canEdit && $article->status !== ArticleStatus::Draft) { ?>
    <a class="article-edit" href="<?=$editUrl?>"><?=strtolower($responseData->getLocalValue('globalEdit'))?></a>
<?php } ?>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('shared/partials/article/article-blog-info', ['responseData' => $responseData]);
} ?>
    <?=$article->contentHtml?>
<?php if ($postBottomContent) { ?>
    <div class="article-blog-footer"><?=$postBottomContent?></div>
<?php } ?>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('shared/partials/article/article-blog-bottom', ['responseData' => $responseData]);
} ?>
  </article>
