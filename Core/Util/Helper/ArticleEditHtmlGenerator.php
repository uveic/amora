<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Model\Util\LookupTableBasicValue;
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
            . '<a href="#" id="pexego-section-button-up-' . $sectionId . '" class="pexego-section-button pexego-section-button-up' . ($isFirst ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveUp') . '" alt="' . $responseData->getLocalValue('sectionMoveUp') . '" src="/img/svg/arrow-fat-up.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-down-' . $sectionId . '" class="pexego-section-button pexego-section-button-down' . ($isLast ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveDown') . '" alt="' . $responseData->getLocalValue('sectionMoveDown') . '" src="/img/svg/arrow-fat-down.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-delete-' . $sectionId . '" class="pexego-section-button pexego-section-button-delete"><img class="img-svg" title="' . $responseData->getLocalValue('sectionRemove') . '" alt="' . $responseData->getLocalValue('sectionRemove') . '" src="/img/svg/trash.svg"></a>' . PHP_EOL
            . '</div>' . PHP_EOL;
    }

    public static function generateSection(
        HtmlResponseDataAuthorised $responseData,
        ArticleSection $articleSection
    ): string {
        if ($articleSection->articleSectionType === ArticleSectionType::TextParagraph) {
            $class = self::getClassName($articleSection->articleSectionType);
            $id = $class . '-' . $articleSection->id;
            return '<section id="' . $id . '" data-editor-id="' . $articleSection->id . '" class="pexego-section pexego-section-paragraph">' . PHP_EOL
                . '<div class="pexego-content-paragraph placeholder" data-placeholder="' . $responseData->getLocalValue('paragraphPlaceholder') . '" contenteditable="true">' . $articleSection->contentHtml . '</div>' . PHP_EOL
                . '</section>';
        }

        $class = 'pexego-section ' . self::getClassName($articleSection->articleSectionType);
        return '<section class="' . $class . '" data-section-id="' . $articleSection->id . '">'  . PHP_EOL
            . $articleSection->contentHtml . PHP_EOL
            . '</section>';
    }

    public static function getArticleTypeId(HtmlResponseDataAuthorised $responseData): int
    {
        if ($responseData->getFirstArticle()) {
            return $responseData->getFirstArticle()->getTypeId();
        }

        $typeIdGetParam = $responseData->getRequest()->getGetParam('articleType');
        if (!empty($typeIdGetParam)) {
            foreach (ArticleType::getAll() as $articleType) {
                if ((int)$typeIdGetParam === $articleType->getId()) {
                    return $articleType->getId();
                }
            }
        }

        return str_contains($responseData->getSiteUrl(), 'articles')
            ? ArticleType::PAGE
            : ArticleType::BLOG;
    }

    public static function generateStatusDropdownSelectHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        $articleTypeId = self::getArticleTypeId($responseData);
        $isHomepage = $articleTypeId === ArticleType::HOMEPAGE;
        $articleStatusId = $responseData->getFirstArticle()
            ? $responseData->getFirstArticle()->getStatusId()
            : ($isHomepage ? ArticleStatus::PUBLISHED->value : ArticleStatus::DRAFT->value);
        $articleStatusName = $responseData->getLocalValue('articleStatus' . ArticleStatus::getNameForId($articleStatusId));
        $isPublished = $responseData->getFirstArticle()
            ? $articleStatusId === ArticleStatus::PUBLISHED->value
            : $isHomepage;
        $random = StringUtil::getRandomString(5);

        $articleStatuses = $isHomepage
            ? [ArticleStatus::getStatusForId(ArticleStatus::PUBLISHED->value)]
            : $responseData->getArticleStatuses();

        $html = '';
        $html .= '<input type="checkbox" id="dropdown-menu-' . $random . '" class="dropdown-menu">';
        $html .= '<div class="dropdown-container">';
        $html .= '<ul>';

        /** @var LookupTableBasicValue $status */
        foreach ($articleStatuses as $status) {
            $html .= '<li><a data-checked="' . ($status->getId() === $articleStatusId ? '1' : '0') .
                '" data-article-status-id="' . $status->getId() .
                '" class="dropdown-menu-option article-status-option ' .
                ($status->getId() === ArticleStatus::PUBLISHED->value ? 'feedback-success' : 'background-light-color') .
                '" href="#">' . $responseData->getLocalValue('articleStatus' . $status->getName()) .
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
        $articleTypeId = self::getArticleTypeId($responseData);

        return $articleTypeId === ArticleType::HOMEPAGE
            ? ''
            : '<a href="#" class="article-settings m-r-1"><img src="/img/svg/gear.svg" class="img-svg m-t-0" alt="' . $responseData->getLocalValue('globalSettings') . '"></a>';
    }

    public static function generateTitleHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        if ($responseData->getFirstArticle()) {
            return $responseData->getLocalValue('globalEdit');
        }

        $articleTypeId = self::getArticleTypeId($responseData);

        if ($articleTypeId === ArticleType::HOMEPAGE) {
            return $responseData->getLocalValue('articleEditHomepageTitle');
        }

        return $articleTypeId === ArticleType::PAGE
            ? $responseData->getLocalValue('globalNew') . ' ' . $responseData->getLocalValue('globalArticle')
            : $responseData->getLocalValue('globalNew') . ' ' . $responseData->getLocalValue('globalBlogPost');
    }

    public static function generateArticleTitleHtml(
        HtmlResponseDataAuthorised $responseData,
        Article $article
    ): string {
        $statusClassname = match ($article->getStatusId()) {
            ArticleStatus::PUBLISHED->value => 'status-published',
            ArticleStatus::PRIVATE->value => 'status-private',
            ArticleStatus::DELETED->value => 'status-deleted',
            ArticleStatus::DRAFT->value => 'status-draft',
            default => ''
        };

        $articleTitle = $article->getTitle() ?: $responseData->getLocalValue('globalNoTitle');
        $articleUrl = UrlBuilderUtil::buildPublicArticleUrl(
            uri: $article->getUri(),
            languageIsoCode: $responseData->getSiteLanguage(),
        );

        $output = '<div class="m-r-05">';
        $output .= '<span class="light-text-color" style="margin-right: 0.1rem;">' . $article->getId() . '. </span>';
        $output .= $article->getStatusId() === ArticleStatus::PUBLISHED->value
            ? '<a href="' . $articleUrl . '">' . $articleTitle . '</a>'
            : $articleTitle;

        if ($article->getPublishOn()) {
            $publishOn = DateUtil::formatDate(
                date: new DateTimeImmutable($article->getPublishOn()),
                lang: $responseData->getSiteLanguage(),
                includeTime: true,
            );
            $output .= '<p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalPublishOn') . '</strong>: ' . $publishOn
                . '</p>';
        } else {
            $updatedAt = DateUtil::formatDate(
                date: new DateTimeImmutable($article->getUpdatedAt()),
                lang: $responseData->getSiteLanguage(),
                includeTime: true,
            );
            $output .= '<p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalUpdatedAt') . '</strong>: ' . $updatedAt
                . '</p>';
        }

        $output .= $article->getTags()
            ? '<p class="article-tags">'
                . '<strong>' . $responseData->getLocalValue('globalTags') . '</strong>: ' . $article->getTagsAsString()
                . '</p>'
            : '';
        $output .= '</div>';

        $output .= '<span class="article-status ' . $statusClassname . '">' .
            $responseData->getLocalValue('articleStatus' . $article->getStatusName()) .
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
