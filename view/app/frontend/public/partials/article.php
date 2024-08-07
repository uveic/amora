<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;

/** @var HtmlResponseData $responseData */
$article = $responseData->article;

if (!isset($article)) {
    return;
}

$postBottomContent = $article->type === ArticleType::Blog
    ? $responseData->postBottomContent?->contentHtml
    : null;

$icon = ArticleHtmlGenerator::generateArticlePublishedIconHtml(
    article: $article,
);

// ToDo: Replace for $article->contentHtml
// ArticleHtmlGenerator::generateArticlePublicContentHtml(articleSections: $responseData->articleSections, indentation: '    ')
?>
  <article>
<?php if ($article->title) {
        echo '    <h1 class="article-title">' . $icon . $article->title . '</h1>' . PHP_EOL;
    }

    if ($article->type === ArticleType::Blog) {
        $publishedOnDate = DateUtil::formatDate(
            date: $article->publishOn ?? $article->updatedAt,
            lang: $responseData->siteLanguage,
            includeWeekDay: false,
        );

        echo '    <p class="article-blog-info">' . $publishedOnDate . '</p>' . PHP_EOL;
    }
?>
<?=$this->insert('partials/article/article-edit', ['responseData' => $responseData]);?>
    <?=$article->contentHtml?>
<?php if ($postBottomContent) { ?>
    <div class="article-blog-footer"><?=$postBottomContent?></div>
<?php } ?>

  </article>
