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

$postBottomContent = $article->type === ArticleType::Blog
    ? $responseData->postBottomContent?->contentHtml
    : null;

?>
  <article>
<?php if ($article->type === ArticleType::Blog) {
    $this->insert('partials/article/article-blog-info', ['responseData' => $responseData]);
}

    if ($article->title) {
        echo '    <h1>' . $article->title . '</h1>' . PHP_EOL;
    }
?>
<?=$this->insert('partials/article/article-edit', ['responseData' => $responseData]);?>
    <?=$article->contentHtml?>
<?php if ($postBottomContent) { ?>
    <div class="article-blog-footer"><?=$postBottomContent?></div>
<?php } ?>
  </article>
