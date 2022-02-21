<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;

final class ArticleEditHtmlGenerator
{
    public static function getClassName(ArticleSectionType $sectionType): string
    {
        return match ($sectionType) {
            ArticleSectionType::TextParagraph => 'pexego-section-paragraph',
            ArticleSectionType::TextTitle => 'pexego-section-title',
            ArticleSectionType::TextSubtitle => 'pexego-section-subtitle',
            ArticleSectionType::Image => 'pexego-section-image',
            ArticleSectionType::YoutubeVideo => 'pexego-section-video'
        };
    }

    public static function getControlButtonsHtml(
        HtmlResponseDataAuthorised $responseData,
        int $sectionId,
        bool $isFirst,
        bool $isLast
    ): string
    {
        return PHP_EOL . '<div class="pexego-section-controls null">' . PHP_EOL
            . '<a href="#" id="pexego-section-button-up-' . $sectionId . '" class="pexego-section-button pexego-section-button-up' . ($isFirst ? ' null' : '') . '"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionMoveUp') . '" alt="' . $responseData->getLocalValue('sectionMoveUp') . '" src="/img/svg/arrow-fat-up.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-down-' . $sectionId . '" class="pexego-section-button pexego-section-button-down' . ($isLast ? ' null' : '') . '"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionMoveDown') . '" alt="' . $responseData->getLocalValue('sectionMoveDown') . '" src="/img/svg/arrow-fat-down.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-delete-' . $sectionId . '" class="pexego-section-button pexego-section-button-delete"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionRemove') . '" alt="' . $responseData->getLocalValue('sectionRemove') . '" src="/img/svg/trash.svg"></a>' . PHP_EOL
            . '</div>' . PHP_EOL;
    }

    public static function generateSection(
        HtmlResponseDataAuthorised $responseData,
        ArticleSection $articleSection
    ): string {
        if ($articleSection->articleSectionType === ArticleSectionType::TextParagraph) {
            $class = self::getClassName($articleSection->articleSectionType);
            $id = $class . '-' . $articleSection->id;
            $contentHtml = strlen($articleSection->contentHtml) > 0
                ? $articleSection->contentHtml
                : $responseData->getLocalValue('paragraphPlaceholder');
            $placeholderClass = strlen($articleSection->contentHtml) > 0 ? '' : ' pexego-section-paragraph-placeholder';
            return '<section id="' . $id . '" data-editor-id="' . $articleSection->id . '" class="pexego-section pexego-section-paragraph">' . PHP_EOL
                . '<div class="pexego-content-paragraph' . $placeholderClass . '" data-placeholder="' . $responseData->getLocalValue('paragraphPlaceholder') . '" spellcheck="true" autocapitalize="sentences" translate="no" role="textbox" aria-multiline="true" contenteditable="true"><p>' . $contentHtml . '</p></div>' . PHP_EOL
                . '</section>';
        }

        $class = 'pexego-section ' . self::getClassName($articleSection->articleSectionType);
        return '<section class="' . $class . '" data-section-id="' . $articleSection->id . '">'  . PHP_EOL
            . $articleSection->contentHtml . PHP_EOL
            . '</section>';
    }

    public static function getArticleType(HtmlResponseDataAuthorised $responseData): ArticleType
    {
        if ($responseData->getFirstArticle()) {
            return $responseData->getFirstArticle()->type;
        }

        $typeIdGetParam = $responseData->getRequest()->getGetParam('articleType');
        if (!empty($typeIdGetParam)) {
            /** @var \BackedEnum $articleType */
            foreach (ArticleType::getAll() as $articleType) {
                if ((int)$typeIdGetParam === $articleType->value) {
                    return $articleType;
                }
            }
        }

        return str_contains($responseData->getSiteUrl(), 'articles')
            ? ArticleType::Page
            : ArticleType::Blog;
    }

    public static function generateStatusDropdownSelectHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        $isHomepage = self::getArticleType($responseData) === ArticleType::Homepage;
        $articleStatus = $responseData->getFirstArticle()
            ? $responseData->getFirstArticle()->status
            : ($isHomepage ? ArticleStatus::Published : ArticleStatus::Draft);
        $articleStatusName = $responseData->getLocalValue('articleStatus' . $articleStatus->name);
        $isPublished = $responseData->getFirstArticle()
            ? $articleStatus === ArticleStatus::Published
            : $isHomepage;
        $random = StringUtil::getRandomString(5);

        $html = '<input type="checkbox" id="dropdown-menu-' . $random . '" class="dropdown-menu">';
        $html .= '<div class="dropdown-container">';
        $html .= '<ul>';

        /** @var \BackedEnum $status */
        foreach (ArticleStatus::getAll() as $status) {
            $html .= '<li><a data-checked="' . ($status === $articleStatus ? '1' : '0') .
                '" data-article-status-id="' . $status->value .
                '" class="dropdown-menu-option article-status-option ' .
                ($status === ArticleStatus::Published ? 'feedback-success' : 'background-light-color') .
                '" href="#">' . $responseData->getLocalValue('articleStatus' . $status->name) .
                '</a></li>';
        }

        $html .= '</ul>';
        $html .= '<label for="dropdown-menu-' . $random . '" class="dropdown-menu-label ' . ($isPublished ? 'feedback-success' : 'background-light-color') . '">';
        $html .= '<span>' . $articleStatusName . '</span>';
        $html .= '<img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down.svg" alt="Change">';
        $html .= '</label>';
        $html .= '</div>';

        return $html;
    }

    public static function generateSettingsButtonHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        $articleType = self::getArticleType($responseData);

        return $articleType === ArticleType::Homepage
            ? ''
            : '<a href="#" class="article-settings m-r-1"><img src="/img/svg/gear.svg" class="img-svg m-t-0" alt="' . $responseData->getLocalValue('globalSettings') . '"></a>';
    }

    public static function generateTitleHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        if ($responseData->getFirstArticle()) {
            return $responseData->getLocalValue('globalEdit');
        }

        $articleType = self::getArticleType($responseData);

        if ($articleType === ArticleType::Homepage) {
            return $responseData->getLocalValue('articleEditHomepageTitle');
        }

        return $articleType === ArticleType::Page
            ? $responseData->getLocalValue('globalNew') . ' ' . $responseData->getLocalValue('globalArticle')
            : $responseData->getLocalValue('globalNew') . ' ' . $responseData->getLocalValue('globalBlogPost');
    }

    public static function generateArticleTitleHtml(
        HtmlResponseDataAuthorised $responseData,
        Article $article
    ): string {
        $statusClassname = match ($article->status) {
            ArticleStatus::Published => 'status-published',
            ArticleStatus::Private => 'status-private',
            ArticleStatus::Deleted => 'status-deleted',
            ArticleStatus::Draft => 'status-draft',
        };

        $articleTitle = $article->title ?: $responseData->getLocalValue('globalNoTitle');
        $articleUrl = UrlBuilderUtil::buildPublicArticleUrl(
            uri: $article->uri,
            languageIsoCode: $responseData->getSiteLanguage(),
        );

        $output = '<div class="m-r-05">';
        $output .= '<span class="light-text-color" style="margin-right: 0.1rem;">' . $article->id . '. </span>';
        $output .= $article->status === ArticleStatus::Published
            ? '<a href="' . $articleUrl . '">' . $articleTitle . '</a>'
            : $articleTitle;

        if ($article->publishOn) {
            $publishOn = DateUtil::formatDate(
                date: $article->publishOn,
                lang: $responseData->getSiteLanguage(),
                includeTime: true,
            );
            $output .= '<p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalPublishOn') . '</strong>: ' . $publishOn
                . '</p>';
        } else {
            $updatedAt = DateUtil::formatDate(
                date: $article->updatedAt,
                lang: $responseData->getSiteLanguage(),
                includeTime: true,
            );
            $output .= '<p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalUpdatedAt') . '</strong>: ' . $updatedAt
                . '</p>';
        }

        $output .= $article->tags
            ? '<p class="article-tags">'
                . '<strong>' . $responseData->getLocalValue('globalTags') . '</strong>: ' . $article->getTagsAsString()
                . '</p>'
            : '';
        $output .= '</div>';

        $output .= '<span class="article-status ' . $statusClassname . '">' .
            $responseData->getLocalValue('articleStatus' . $article->status->name) .
            '</span>';

        return $output;
    }

    public static function generateArticlePublishedIconHtml(Article $article): string
    {
        return $article->isPublished()
            ? ''
            : '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/lock.svg" alt="">';
    }
}
