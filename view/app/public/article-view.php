<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

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

$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

$this->layout('base', ['responseData' => $responseData]);

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

        echo '    <div class="article-blog-info">' . $publishedOnDate . '</div>' . PHP_EOL;
    }

    if ($isAdmin) {
        echo  '    <div><a class="content-edit-button" href="' . UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $responseData->article->id) . '">' . strtolower($responseData->getLocalValue('globalEdit')) . '</a></div>' . PHP_EOL;
    }

    echo '    ' . $article->contentHtml . PHP_EOL;
?>
<?php if ($postBottomContent) { ?>
    <div class="article-blog-footer"><?=$postBottomContent?></div>
<?php } ?>

  </article>
