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
use Amora\App\Value\Language;

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
        HtmlResponseDataAdmin $responseData,
        int                   $sectionId,
        bool                  $isFirst,
        bool                  $isLast,
    ): string {
        $output = [];
        $output[] = '          <div class="pexego-section-controls null">';
        $output[] = '            <a href="#" id="pexego-section-button-up-' . $sectionId . '" class="pexego-section-button pexego-section-button-up' . ($isFirst ? ' null' : '') . '"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionMoveUp') . '" alt="' . $responseData->getLocalValue('sectionMoveUp') . '" src="/img/svg/arrow-fat-up.svg"></a>';
        $output[] = '            <a href="#" id="pexego-section-button-down-' . $sectionId . '" class="pexego-section-button pexego-section-button-down' . ($isLast ? ' null' : '') . '"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionMoveDown') . '" alt="' . $responseData->getLocalValue('sectionMoveDown') . '" src="/img/svg/arrow-fat-down.svg"></a>';
        $output[] = '            <a href="#" id="pexego-section-button-delete-' . $sectionId . '" class="pexego-section-button pexego-section-button-delete"><img class="img-svg img-svg-30" title="' . $responseData->getLocalValue('sectionRemove') . '" alt="' . $responseData->getLocalValue('sectionRemove') . '" src="/img/svg/trash.svg"></a>';
        $output[] = '          </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateSection(
        HtmlResponseDataAdmin $responseData,
        ArticleSection        $articleSection,
    ): string {
        if ($articleSection->articleSectionType === ArticleSectionType::TextParagraph) {
            $class = self::getClassName($articleSection->articleSectionType);
            $id = $class . '-' . $articleSection->id;
            $contentHtml = strlen($articleSection->contentHtml) > 0
                ? $articleSection->contentHtml
                : $responseData->getLocalValue('paragraphPlaceholder');
            $placeholderClass = strlen($articleSection->contentHtml) > 0 ? '' : ' pexego-section-paragraph-placeholder';

            $output = [];
            $output[] = '          <section id="' . $id . '" data-editor-id="' . $articleSection->id . '" class="pexego-section pexego-section-paragraph">';
            $output[] = '            <div class="pexego-content-paragraph' . $placeholderClass . '" data-placeholder="' . $responseData->getLocalValue('paragraphPlaceholder') . '" spellcheck="true" autocapitalize="sentences" translate="no" role="textbox" aria-multiline="true" contenteditable="true">';
            $output[] = '              <p>' . $contentHtml . '</p>';
            $output[] = '            </div>';
            $output[] = '          </section>';

            return implode(PHP_EOL, $output) . PHP_EOL;
        }

        $class = 'pexego-section ' . self::getClassName($articleSection->articleSectionType);
        $output = [];
        $output[] = '          <section class="' . $class . '" data-section-id="' . $articleSection->id . '">';
        $output[] = '            ' . $articleSection->contentHtml;
        $output[] = '          </section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

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
        $isPartialContent = ArticleType::isPartialContent(self::getArticleType($responseData));
        $articleStatus = $responseData->article?->status
            ?? ($isPartialContent ? ArticleStatus::Published : ArticleStatus::Draft);
        $articleStatusName = $responseData->getLocalValue('articleStatus' . $articleStatus->name);
        $isPublished = $responseData->article
            ? $articleStatus === ArticleStatus::Published
            : $isPartialContent;
        $displayClass = $isPartialContent ? ' null' : '';

        $output = [];
        $output[] = '      <input type="checkbox" id="article-status-dd-checkbox" class="dropdown-menu">';
        $output[] = '      <div class="dropdown-container article-status-container' . $displayClass . '">';
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
        $displayClass = ArticleType::isPartialContent(self::getArticleType($responseData)) ? ' null' : '';

        $output = [];
        $output[] = '      <input type="checkbox" id="article-lang-dd-checkbox" class="dropdown-menu">';
        $output[] = '      <div class="dropdown-container article-lang-container' . $displayClass . '">';
        $output[] = '        <ul>';

        /** @var \BackedEnum $language */
        foreach (Core::getAllLanguages() as $language) {
            $output[] = '          <li><a data-checked="' . ($language === $articleLanguage ? '1' : '0') .
                '" data-value="' . $language->value .
                '" class="dropdown-menu-option article-lang-dd-option background-light-text-color"' .
                ' href="#">' . Language::getIconFlag($language, 'm-r-05') . $language->name . '</a></li>';
        }

        $output[] = '        </ul>';
        $output[] = '        <label id="article-lang-dd-label" for="article-lang-dd-checkbox" class="dropdown-menu-label background-light-text-color">';
        $output[] = '          <span>' . Language::getIconFlag($articleLanguage, 'm-r-05') . $articleLanguage->name . '</span>';
        $output[] = '          <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Change">';
        $output[] = '        </label>';
        $output[] = '      </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateSettingsButtonHtml(
        HtmlResponseDataAdmin $responseData
    ): string {
        $articleType = self::getArticleType($responseData);

        return ArticleType::isPartialContent($articleType)
            ? ''
            : '<a href="#" class="article-settings"><img src="/img/svg/gear.svg" class="img-svg img-svg-25" alt="' . $responseData->getLocalValue('globalSettings') . '"></a>';
    }

    public static function generateTitleHtml(
        HtmlResponseDataAdmin $responseData,
    ): string {
        $article = $responseData->article;
        $articleType = $article ? $article->type : self::getArticleType($responseData);
        $langIcon = $article
            ? Language::getIconFlag($article->language)
            : Language::getIconFlag($responseData->siteLanguage);

        $articleId = $article
            ? '<span style="color: var(--light-color);font-size: 0.9rem;font-weight: normal;">#' . $article->id . '</span>'
            : '';

        $verb = $article
            ? $responseData->getLocalValue('globalEdit')
            : $responseData->getLocalValue('globalNew');

        return '<span style="display:flex;flex-flow:row wrap;gap:0.5rem;align-items:center;">'
            . $articleId
            . '<span style="padding: 0.1rem 0.4rem;">' . $verb . '</span>'
            . '<span style="padding: 0.1rem 0.4rem;">' . $responseData->getLocalValue('articleType' . $articleType->name) . '</span>'
            . '<span style="padding: 0.1rem 0.4rem;">' . $langIcon . '</span>'
            . '</span>';
    }

    public static function generateArticleRowHtml(
        HtmlResponseDataAdmin $responseData,
        Article               $article,
    ): string {
        $statusClassname = match ($article->status) {
            ArticleStatus::Published => 'status-published',
            ArticleStatus::Private => 'status-private',
            ArticleStatus::Deleted => 'status-deleted',
            ArticleStatus::Draft => 'status-draft',
        };

        $articleUrl = UrlBuilderUtil::buildPublicArticlePath(
            path: $article->path,
            language: $responseData->siteLanguage,
        );
        $articleTitle = ArticleType::isPartialContent($article->type)
            ? $responseData->getLocalValue('globalNoTitle')
            : match ($article->status) {
                ArticleStatus::Published => '<a href="' . $articleUrl . '">' . $article->title . '</a>',
                default => $article->title ?: $responseData->getLocalValue('globalNoTitle'),
            };

        $output = [];
        $output[] = '            <div class="m-r-05">';
        $output[] = '              <span class="light-text-color" style="font-size: 0.9rem;">#' . $article->id . '</span>';
        $output[] = '              ' . Language::getIconFlag($article->language, 'm-l-05 m-r-05');
        $output[] = '              ' . $articleTitle;

        if ($article->publishOn) {
            $publishOn = DateUtil::formatDate(
                date: $article->publishOn,
                lang: $responseData->siteLanguage,
                includeTime: true,
            );
            $output[] = '              <p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalPublishOn') . '</strong>: ' . $publishOn
                . '</p>';
        } else {
            $updatedAt = DateUtil::formatDate(
                date: $article->updatedAt,
                lang: $responseData->siteLanguage,
                includeTime: true,
            );
            $output[] = '              <p class="article-tags"><strong>'
                . $responseData->getLocalValue('globalUpdatedAt') . '</strong>: ' . $updatedAt
                . '</p>';
        }

        if ($article->tags) {
            $output[] = '              <p class="article-tags">'
                . '<strong>' . $responseData->getLocalValue('globalTags') . '</strong>: ' . $article->getTagsAsString()
                . '</p>';
        }

        $output[] = '            </div>';

        $output[] = '            <span class="article-status ' . $statusClassname . '">' .
            $responseData->getLocalValue('articleStatus' . $article->status->name) .
            '</span>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateArticlePublishedIconHtml(Article $article): string
    {
        return $article->isPublished()
            ? ''
            : '<img class="img-svg m-r-05" width="20" height="20" src="/img/svg/lock.svg" alt="">';
    }
}
