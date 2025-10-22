<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;
use BackedEnum;

final class ArticleHtmlGenerator
{
    public static function getArticleType(HtmlResponseDataAdmin $responseData): ArticleType
    {
        if ($responseData->article) {
            return $responseData->article->type;
        }

        $typeIdGetParam = $responseData->request->getGetParam('atId');
        if (!empty($typeIdGetParam)) {
            /** @var BackedEnum $articleType */
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

        $output = [];
        $output[] = '      <input type="checkbox" id="article-status-dd-checkbox" class="dropdown-menu">';
        $output[] = '      <div class="dropdown-container article-status-container">';
        $output[] = '        <label for="article-status-dd-checkbox" class="label">' . $responseData->getLocalValue('globalStatus') . ':</label>';
        $output[] = '        <ul>';

        /** @var BackedEnum $status */
        foreach (ArticleStatus::getAll() as $status) {
            $output[] = '          <li><a data-checked="' . ($status === $articleStatus ? '1' : '0') .
                '" data-value="' . $status->value .
                '" class="dropdown-menu-option article-status-dd-option ' . $status->getClass() . '"' .
                ' href="#" data-dropdown-identifier="article-status">' . $responseData->getLocalValue('articleStatus' . $status->name) .
                '</a></li>';
        }

        $output[] = '        </ul>';
        $output[] = '        <label id="article-status-dd-label" for="article-status-dd-checkbox" class="dropdown-menu-label ' . $articleStatus->getClass() . '">';
        $output[] = '          <span>' . $articleStatusName . '</span>';
        $output[] = '          ' . CoreIcons::CARET_DOWN;
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
        $output[] = '        <label for="article-lang-dd-checkbox" class="label">' . $responseData->getLocalValue('globalLanguage') . ':</label>';
        $output[] = '        <ul>';

        /** @var BackedEnum $language */
        foreach (Core::getEnabledSiteLanguages() as $language) {
            $output[] = '          <li><a data-checked="' . ($language === $articleLanguage ? '1' : '0') .
                '" data-value="' . $language->value .
                '" class="dropdown-menu-option article-lang-dd-option"' .
                ' href="#" data-dropdown-identifier="article-lang">' . $language->getIconFlag('m-r-05') . $language->name . '</a></li>';
        }

        $output[] = '        </ul>';
        $output[] = '        <label id="article-lang-dd-label" for="article-lang-dd-checkbox" class="dropdown-menu-label">';
        $output[] = '          <span>' . $articleLanguage->getIconFlag('m-r-05') . $articleLanguage->name . '</span>';
        $output[] = '          ' . CoreIcons::CARET_DOWN;
        $output[] = '        </label>';
        $output[] = '      </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateArticleRowHtml(
        HtmlResponseDataAdmin $responseData,
        Article $article,
        string $indentation = '        ',
    ): string {
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

        $articlePublicLinkHtml = $article->status->isPublic()
            ? '<a href="' . $articlePublicUrl . '">' . CoreIcons::ARROW_SQUARE_OUT . '</a>'
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
        $output[] = $indentation . '      ' . $article->language->getIconFlag('m-l-05 m-r-05');
        $output[] = $indentation . '      ' . $articleTitleHtml;
        $output[] = $indentation . '      ' . $articlePublicLinkHtml;
        $output[] = $indentation . '    </div>';
        if ($article->tags) {
            $output[] = $indentation . '    <div class="article-tags">' . CoreIcons::TAG . $article->getTagsAsString() . '</div>';
        }
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="table-item flex-no-grow">';
        $output[] = $indentation . '    <span class="article-status ' . $statusClassname . '">' . $responseData->getLocalValue('articleStatus' . $article->status->name) . '</span>';
        $output[] = $indentation . '    <div>' . CoreIcons::CALENDAR_CHECK .  $articleDate . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateArticlePublishedIconHtml(Article $article): string
    {
        if ($article->status === ArticleStatus::Unlisted) {
            return CoreIcons::EYE_CLOSED;
        }

        return $article->isPublished() ? '' : CoreIcons::LOCK;
    }

    public static function generateArticlePublicContentHtml(
        array $articleSections,
        string $indentation = '',
    ): string {
        $output = [];

        /** @var ArticleSection $section */
        foreach ($articleSections as $section) {
            $output[] = $indentation . match ($section->type) {
                ArticleSectionType::Image => self::generateMediaHtml($section),
                ArticleSectionType::TextParagraph => '<p>' . $section->contentHtml . '</p>',
                default => $section->contentHtml,
            };
        }

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    private static function generateMediaHtml(ArticleSection $section): string
    {
        $html = $section->media->asHtml(
            sizes: '(min-width: 860px) 800px, calc(92.59vw + 22px)',
        );

        $captionHtml = '';
        if ($section->mediaCaption) {
            $captionHtml = '<div class="article-section-image-caption">' . $section->mediaCaption . '</div>';
        }

        return $html . $captionHtml;
    }
}
