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

$postBottomContent = $responseData->postBottomContent ? $responseData->postBottomContent->contentHtml : '';

?>
  <article>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('partials/article/article-blog-info', ['responseData' => $responseData]);
} ?>
    <?=$article->contentHtml?>
<?php if ($postBottomContent) { ?>
    <div class="article-blog-footer"><?=$postBottomContent?></div>
<?php } ?>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('partials/article/article-blog-bottom', ['responseData' => $responseData]);
} ?>
  </article>
