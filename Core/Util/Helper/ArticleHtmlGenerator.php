<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\App\Value\Language;

final class ArticleHtmlGenerator
{
    public static function getArticleType(HtmlResponseDataAdmin $responseData): ArticleType
    {
        if ($responseData->article) {
            return $responseData->article->type;
        }

        $typeIdGetParam = $responseData->request->getGetParam('atId');
        if (!empty($typeIdGetParam)) {
            /** @var \BackedEnum $articleType */
            foreach (ArticleType::getAll() as $articleType) {
                if ((int)$typeIdGetParam === $articleType->value) {
                    return $articleType;
                }
            }
        }

        return str_contains($responseData->siteUrl, 'articles')
            ? ArticleType::Page
            : ArticleType::Blog;
    }

    public static function generateArticleStatusDropdownSelectHtml(
        HtmlResponseDataAdmin $responseData,
    ): string {
        $articleStatus = $responseData->article?->status ?? ArticleStatus::Draft;
        $articleStatusName = $responseData->getLocalValue('articleStatus' . $articleStatus->name);
        $isPublished = $responseData->article && $articleStatus === ArticleStatus::Published;

        $output = [];
        $output[] = '      <input type="checkbox" id="article-status-dd-checkbox" class="dropdown-menu">';
        $output[] = '      <div class="dropdown-container article-status-container">';
        $output[] = '        <label for="article-status-dd-checkbox" class="label">' . $responseData->getLocalValue('globalStatus') . ':</label>';
        $output[] = '        <ul>';

        /** @var \BackedEnum $status */
        foreach (ArticleStatus::getAll() as $status) {
            $output[] = '          <li><a data-checked="' . ($status === $articleStatus ? '1' : '0') .
                '" data-value="' . $status->value .
                '" class="dropdown-menu-option article-status-dd-option ' .
                ($status === ArticleStatus::Published ? 'feedback-success' : 'background-light-text-color') .
                '" href="#">' . $responseData->getLocalValue('articleStatus' . $status->name) .
                '</a></li>';
        }

        $output[] = '        </ul>';
        $output[] = '        <label id="article-status-dd-label" for="article-status-dd-checkbox" class="dropdown-menu-label ' . ($isPublished ? 'feedback-success' : 'background-light-text-color') . '">';
        $output[] = '          <span>' . $articleStatusName . '</span>';
        $output[] = '          <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Change">';
        $output[] = '        </label>';
        $output[] = '      </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateArticleLanguageDropdownSelectHtml(
        HtmlResponseDataAdmin $responseData,
    ): string {
        $articleLanguage = $responseData->article?->language ?? $responseData->siteLanguage;

        $output = [];
        $output[] = '      <input type="checkbox" id="article-lang-dd-checkbox" class="dropdown-menu">';
        $output[] = '      <div class="dropdown-container article-lang-container">';
        $output[] = '        <label for="article-status-dd-checkbox" class="label">' . $responseData->getLocalValue('globalLanguage') . ':</label>';
        $output[] = '        <ul>';

        /** @var \BackedEnum $language */
        foreach (Core::getAllLanguages() as $language) {
            $output[] = '          <li><a data-checked="' . ($language === $articleLanguage ? '1' : '0') .
                '" data-value="' . $language->value .
                '" class="dropdown-menu-option article-lang-dd-option background-light-text-color"' .
                ' href="#">' . $language->getIconFlag('m-r-05') . $language->name . '</a></li>';
        }

        $output[] = '        </ul>';
        $output[] = '        <label id="article-lang-dd-label" for="article-lang-dd-checkbox" class="dropdown-menu-label background-light-text-color">';
        $output[] = '          <span>' . $articleLanguage->getIconFlag('m-r-05') . $articleLanguage->name . '</span>';
        $output[] = '          <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Change">';
        $output[] = '        </label>';
        $output[] = '      </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateSettingsButtonHtml(
        HtmlResponseDataAdmin $responseData,
    ): string {
        return '<a href="#" class="article-settings"><img src="/img/svg/gear.svg" class="img-svg img-svg-25" alt="' . $responseData->getLocalValue('globalSettings') . '"></a>';
    }

    public static function generateArticleRowHtml(
        HtmlResponseDataAdmin $responseData,
        Article $article,
        string $indentation = '        ',
    ): string
    {
        $statusClassname = match ($article->status) {
            ArticleStatus::Published => 'status-published',
            ArticleStatus::Private, ArticleStatus::Unlisted => 'status-private',
            ArticleStatus::Deleted => 'status-deleted',
            ArticleStatus::Draft => 'status-draft',
        };

        $articleEditUrl = UrlBuilderUtil::buildBackofficeArticleUrl(
            language: $responseData->siteLanguage,
            articleId: $article->id,
        );

        $articlePublicUrl = UrlBuilderUtil::buildPublicArticlePath(
            path: $article->path,
            language: $responseData->siteLanguage,
        );
        $articleTitleHtml = $article->title
            ? '<a href="' . $articleEditUrl . '">' . $article->title . '</a>'
            : '<a href="' . $articleEditUrl . '">' . $responseData->getLocalValue('globalNoTitle') . '</a>';

        $articlePublicLinkHtml = ArticleStatus::isPublic($article->status)
            ? '<a href="' . $articlePublicUrl . '"><img src="/img/svg/arrow-square-out.svg" class="img-svg m-l-05" alt="Public link" width="20" height="20"></a>'
            : '';

        $articleDate = $article->publishOn
            ? DateUtil::formatDate(
                date: $article->publishOn,
                lang: $responseData->siteLanguage,
                includeTime: true,
            )
            : DateUtil::formatDate(
                date: $article->updatedAt,
                lang: $responseData->siteLanguage,
                includeTime: true,
            );

        $output = [];
        $output[] = $indentation . '<div class="table-row">';
        $output[] = $indentation . '  <div class="table-item table-item-flex-column">';
        $output[] = $indentation . '    <div>';
        $output[] = $indentation . '      <span class="light-text-color font-0-9">#' . $article->id . '</span>';
        $output[] = $indentation . '      ' . $article->language->getIconFlag('m-l-05 m-r-05');
        $output[] = $indentation . '      ' . $articleTitleHtml;
        $output[] = $indentation . '      ' . $articlePublicLinkHtml;
        $output[] = $indentation . '    </div>';
        if ($article->tags) {
            $output[] = $indentation . '    <div class="article-tags">'
                . '<img class="img-svg m-r-05" src="/img/svg/tag.svg" alt="Tags" width="20" height="20">' . $article->getTagsAsString()
                . '</div>';
        }
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="table-item flex-no-grow">';
        $output[] = $indentation . '    <span class="article-status ' . $statusClassname . '">' . $responseData->getLocalValue('articleStatus' . $article->status->name) . '</span>';
        $output[] = $indentation . '    <div><img class="img-svg m-r-05" src="/img/svg/calendar-check.svg" alt="Recibido" width="20" height="20">' .  $articleDate . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateArticlePublishedIconHtml(Article $article): string
    {
        if ($article->status === ArticleStatus::Unlisted) {
            return '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/eye-closed.svg" alt="Unlisted">';
        }

        return $article->isPublished()
            ? ''
            : '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/lock.svg" alt="Lock">';
    }
}
