<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Model\Util\LookupTableBasicValue;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\StringUtil;

final class ArticleEditHtmlGenerator
{
    public static function getClassName(int $sectionTypeId): string
    {
        return match ($sectionTypeId) {
            ArticleSectionType::TEXT_PARAGRAPH => 'pexego-section-paragraph',
            ArticleSectionType::TEXT_TITLE => 'pexego-section-title',
            ArticleSectionType::TEXT_SUBTITLE => 'pexego-section-subtitle',
            ArticleSectionType::IMAGE => 'pexego-section-image',
            ArticleSectionType::YOUTUBE_VIDEO => 'pexego-section-video'
        };
    }

    public static function getControlButtonsHtml(
        HtmlResponseDataAuthorised $responseData,
        int $sectionId,
        bool $isFirst,
        bool $isLast
    ): string
    {
        return PHP_EOL . '<div class="pexego-section-controls">' . PHP_EOL
            . '<a href="#" id="pexego-section-button-up-' . $sectionId . '" class="pexego-section-button pexego-section-button-up' . ($isFirst ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveUp') . '" alt="' . $responseData->getLocalValue('sectionMoveUp') . '" src="/img/svg/arrow-fat-up.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-down-' . $sectionId . '" class="pexego-section-button pexego-section-button-down' . ($isLast ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveDown') . '" alt="' . $responseData->getLocalValue('sectionMoveDown') . '" src="/img/svg/arrow-fat-down.svg"></a>' . PHP_EOL
            . '<a href="#" id="pexego-section-button-delete-' . $sectionId . '" class="pexego-section-button pexego-section-button-delete"><img class="img-svg" title="' . $responseData->getLocalValue('sectionRemove') . '" alt="' . $responseData->getLocalValue('sectionRemove') . '" src="/img/svg/trash.svg"></a>' . PHP_EOL
            . '</div>' . PHP_EOL;
    }

    public static function generateSection(
        HtmlResponseDataAuthorised $responseData,
        ArticleSection $articleSection
    ): string {
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
            $class = self::getClassName($articleSection->getArticleSectionTypeId());
            $id = $class . '-' . $articleSection->getId();
            return '<section id="' . $id . '" data-editor-id="' . $articleSection->getId() . '" class="pexego-section pexego-section-paragraph">' . PHP_EOL
                . '<div class="pexego-content-paragraph placeholder" data-placeholder="' . $responseData->getLocalValue('paragraphPlaceholder') . '" contenteditable="true">' . $articleSection->getContentHtml() . '</div>' . PHP_EOL
                . '</section>';
        }

        $class = 'pexego-section ' . self::getClassName($articleSection->getArticleSectionTypeId());
        return '<section class="' . $class . '" data-section-id="' . $articleSection->getId() . '">'  . PHP_EOL
            . $articleSection->getContentHtml() . PHP_EOL
            . '</section>';
    }

    public static function getArticleTypeId(HtmlResponseDataAuthorised $responseData): int
    {
        if ($responseData->getFirstArticle()) {
            return $responseData->getFirstArticle()->getTypeId();
        }

        $typeIdGetParam = $responseData->getRequest()->getGetParam('articleType');
        if (empty($typeIdGetParam)) {
            return ArticleType::ARTICLE;
        }

        foreach (ArticleType::getAll() as $articleType) {
            if ((int)$typeIdGetParam === $articleType->getId()) {
                return $articleType->getId();
            }
        }

        return ArticleType::ARTICLE;
    }

    public static function generateStatusDropdownSelectHtml(
        HtmlResponseDataAuthorised $responseData
    ): string {
        $articleTypeId = self::getArticleTypeId($responseData);
        $isHomepage = $articleTypeId === ArticleType::HOMEPAGE;
        $articleStatusId = $responseData->getFirstArticle()
            ? $responseData->getFirstArticle()->getStatusId()
            : ($isHomepage ? ArticleStatus::PUBLISHED : ArticleStatus::DRAFT);
        $articleStatusName = $responseData->getLocalValue('articleStatus' . ArticleStatus::getNameForId($articleStatusId));
        $isPublished = $responseData->getFirstArticle()
            ? $articleStatusId === ArticleStatus::PUBLISHED
            : $isHomepage;
        $random = StringUtil::getRandomString(5);

        $articleStatuses = $isHomepage
            ? [ArticleStatus::getStatusForId(ArticleStatus::PUBLISHED)]
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
                ($status->getId() === ArticleStatus::PUBLISHED ? 'feedback-success' : 'background-light-color') .
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
        $articleTypeId = self::getArticleTypeId($responseData);

        return $articleTypeId === ArticleType::HOMEPAGE
            ? $responseData->getLocalValue('articleEditHomepageTitle')
            : ($responseData->getFirstArticle()
                ? $responseData->getLocalValue('globalEdit')
                : $responseData->getLocalValue('globalNew') . ' ' .
                $responseData->getLocalValue('globalArticle'));
    }
}
