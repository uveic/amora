<?php

namespace Amora\Core\Util\Helper;

use Amora\App\Value\Language;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Model\AlbumSectionMedia;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Util\UrlBuilderUtil;

final class AlbumHtmlGenerator
{
    public static function generateAlbumStatusHtml(AlbumStatus $status): string
    {
        return '<span class="article-status m-t-0 '
            . $status->getClass() . '">'
            . $status->getIcon()
            . $status->getName()
            . '</span>' . PHP_EOL;
    }

    public static function generateDynamicAlbumStatusHtml(
        AlbumStatus $albumStatus,
        string $indentation = '',
    ): string {
        $output = [
            $indentation . '<input type="checkbox" id="album-status-dd-checkbox" class="dropdown-menu">',
            $indentation . '<div class="dropdown-container album-status-container">',
            $indentation . '  <ul>',
        ];

        foreach (AlbumStatus::getAll() as $item) {
            $statusClassname = $item->getClass();
            $icon = $item->getIcon();
            $output[] = $indentation . '    <li><a data-checked="' . ($albumStatus === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option album-status-dd-option ' . $statusClassname . '" href="#">' . $icon . $item->getName() . '</a></li>';
        }

        $icon = $albumStatus->getIcon();
        $selectedStatusClassname = $albumStatus->getClass();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="album-status-dd-label" for="album-status-dd-checkbox" data-status-id="' . $albumStatus->value . '" class="dropdown-menu-label ' . $selectedStatusClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $albumStatus->getName() . '</span>';
        $output[] = $indentation . '    <img class="img-svg no-margin" width="20" height="20" src="/img/svg/caret-down-white.svg" alt="Change">';
        $output[] = $indentation . '  </label>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumRowHtml(
        HtmlResponseDataAdmin $responseData,
        Album $album,
        string $indentation = '',
    ): string {
        $albumEditUrl = UrlBuilderUtil::buildBackofficeAlbumViewUrl(
            language: $responseData->siteLanguage,
            albumId: $album->id,
        );

        $albumPublicUrl = UrlBuilderUtil::buildPublicAlbumUrl(
            slug: $album->slug->slug,
            language: $responseData->siteLanguage,
        );

        $albumPublicLinkHtml = $album->status->isPublished()
            ? '<a href="' . $albumPublicUrl . '"><img src="/img/svg/arrow-square-out.svg" class="img-svg m-l-05" alt="Public link" width="20" height="20"></a>'
            : '';

        $output = [];
        $output[] = $indentation . '<div class="album-card-item">';
        $output[] = $indentation . '  <div class="album-image">';
        $output[] = $indentation . '    <a href="' . $albumEditUrl . '"><img src="' . $album->mainMedia->getPathWithNameSmall() . '" alt="' . $album->mainMedia->buildAltText() . '"></a>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="album-card-content-wrapper">';

        $output[] = $indentation . '    <div class="album-card-content">';
        $output[] = $indentation . '      <a href="' . $albumEditUrl . '" class="album-title">' . $album->titleHtml . '</a>';
        $output[] = $indentation . '      ' . $albumPublicLinkHtml;
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div>';
        $output[] = $indentation . '      ' . self::generateAlbumStatusHtml($album->status);
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumSectionHtml(
        Language $language,
        AlbumSection $section,
        string $indentation = '',
    ): string {
        $localisationUtil = Core::getLocalisationUtil($language);

        $output = [];
        $output[] = $indentation . '<div class="album-section-item" data-album-section-id="' . $section->id . '">';
        $output[] = $indentation . '  <div class="album-section-item-content">';

        $output[] = $indentation . '    <div class="album-section-item-edit-container">';
        $output[] = $indentation . '      <span class="album-section-item-number">#' . $section->sequence . '</span>';
        $output[] = $indentation . '      <img class="img-svg album-section-edit-js" data-album-section-id="' . $section->id . '" width="20" height="20" src="/img/svg/pencil.svg" alt="' . $localisationUtil->getValue('globalEdit') . '">';
        $output[] = $indentation . '      <div class="album-section-button-container-js null">';
        $output[] = $indentation . '        <button class="album-section-button album-section-cancel-js m-r-05" data-album-section-id="' . $section->id . '">' . $localisationUtil->getValue('globalCancel') . '</button>';
        $output[] = $indentation . '        <button class="album-section-button is-success album-section-save-js" data-album-section-id="' . $section->id . '">' . $localisationUtil->getValue('globalSave') . '</button>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <h3 class="section-title-html">' . ($section->titleHtml ?: '-') . '</h3>';
        $output[] = $indentation . '    <div class="section-subtitle-html">' . ($section->subtitleHtml ?: '-') . '</div>';
        $output[] = $indentation . '    <div class="section-content-html">' . ($section->contentHtml ?: '-') . '</div>';

        $output[] = $indentation . '    <div id="album-section-main-media-' . $section->id . '" class="main-image-container main-image-container-full m-t-2">';
        if ($section->mainMedia) {
            $output[] = $indentation . '      <img class="album-section-main-media" data-media-id="' . $section->mainMedia->id . '" src="' . $section->mainMedia->getPathWithNameSmall() . '" alt="' . $section->mainMedia->buildAltText() . '">';
        }

        $output[] = $indentation . '      <div class="main-image-button-container null">';
        $output[] = $indentation . '        <a href="#" class="main-image-button main-image-button-red album-section-main-media-delete-js' . ($section->mainMedia ? '' : ' null') . '" data-media-id="' . $section->mainMedia?->id . '" data-event-listener-action="albumSectionDeleteMainMedia" data-target-container-id="album-section-main-media-' . $section->id . '">';
        $output[] = $indentation . '          <img class="img-svg" src="/img/svg/trash-white.svg" alt="' . $localisationUtil->getValue('globalRemoveImage') . '" title="' . $localisationUtil->getValue('globalRemoveImage') . '">';
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '        <a href="#" class="main-image-button album-section-main-media-js" data-media-id="' . $section->mainMedia?->id . '" data-event-listener-action="albumSectionSelectMainMedia" data-target-container-id="album-section-main-media-' . $section->id . '">';
        $output[] = $indentation . '          <img class="img-svg" src="/img/svg/image.svg" alt="' . $localisationUtil->getValue('globalAddImage') . '" title="' . $localisationUtil->getValue('globalAddImage') . '">';
        $output[] = $indentation . '          <span>' . ($section->mainMedia ? $localisationUtil->getValue('globalModify') : $localisationUtil->getValue('globalAddImage')) . '</span>';
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div id="album-section-item-media-' . $section->id . '" class="album-section-item-media" data-album-section-id="' . $section->id . '">';

        /** @var AlbumSectionMedia $sectionMedia */
        foreach ($section->media as $sectionMedia) {
            $output[] = $indentation . '    ' . self::generateAlbumSectionMediaHtml($sectionMedia);
        }

        $output[] = $indentation . '    <a href="#" class="button is-success select-media-action" data-target-container-id="album-section-item-media-' . $section->id .'" data-event-listener-action="albumSectionAddMedia">';
        $output[] = $indentation . '      <img class="img-svg img-svg-30" width="30" height="30" src="/img/svg/image-white.svg" alt="Image">';
        $output[] = $indentation . '      <span>Engadir</span>';
        $output[] = $indentation . '    </a>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumSectionMediaHtml(
        AlbumSectionMedia $albumSectionMedia,
        string $indentation = '',
    ): string {
        $titleAlt = $albumSectionMedia->media->buildAltText();

        $output = [];
        $output[] = $indentation . '<img src="' . $albumSectionMedia->media->getPathWithNameSmall() . '" alt="' . $titleAlt . '" title="' . $titleAlt . '" data-media-id="' . $albumSectionMedia->media->id . '" data-sequence="' . $albumSectionMedia->sequence . '" class="album-section-image">';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generatePublicAlbumSectionMediaArray(
        array $mediaArray,
        string $indentation = '',
        ?string $cssClass = 'media',
    ): array {
        $output = [];

        $count = 0;
        /** @var AlbumSectionMedia $albumSectionMedia */
        foreach ($mediaArray as $albumSectionMedia) {
            $class = $cssClass . ($count === 0 ? '-active' : '-hidden');
            $lazyLoading = $count === 0 ? '' : ' loading="lazy"';
            $output[] = $indentation . '    <img src="' . $albumSectionMedia->media->getPathWithNameMedium() . '" class="media-item ' . $class . '" alt="' . $albumSectionMedia->buildAltText() . '"' . $lazyLoading . '>';
            $count++;
        }

        return $output;
    }

    public static function generateAlbumTemplateNewYorkFirstSectionHtml(
        ?AlbumSection $section,
        string $indentation = '',
    ): string {
        if (!$section || !$section->media) {
            return '';
        }

        $output = [];
        $output[] = $indentation . '<section class="content-child js-content-slider-fade">';
        $output[] = $indentation . '  <div class="media-wrapper">';

        $output = array_merge(
            $output,
            self::generatePublicAlbumSectionMediaArray(
                mediaArray: $section->media,
                indentation: '    ',
                cssClass: 'media-opacity',
            )
        );

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="media-content-wrapper">';
        $output[] = $indentation . '    <h1 class="media-title main-slide-title">' . $section->titleHtml . '</h1>';
        $output[] = $indentation . '    <div class="media-text">' . $section->contentHtml . '</div>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumTemplateNewYorkSectionHtml(
        AlbumSection $section,
        Language $language,
        string $indentation = '',
    ): string {
        if (!$section->media) {
            return '';
        }

        $localisation = Core::getLocalisationUtil($language);

        $output = [];
        $output[] = $indentation . '<section class="content-child js-content-slider" data-media-id="' . $section->id . '">';
        $output[] = $indentation . '  <div class="media-wrapper">';

        $output = array_merge(
            $output,
            self::generatePublicAlbumSectionMediaArray(
                mediaArray: $section->media,
                indentation: '    ',
            )
        );

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="media-content-wrapper">';
        $output[] = $indentation . '    <div class="content-header">';

        $title = '<span class="number">' . $section->sequence . '.</span> ' . $section->titleHtml;
        $output[] = $indentation . '      <h1 class="media-title">' . $title . '</h1>';
        if ($section->subtitleHtml) {
            $output[] = $indentation . '      <p class="media-subtitle">' . $section->subtitleHtml . '</p>';
        }
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '    <div class="content-text">';
        $output[] = $indentation . '      <div class="media-text">' . $section->contentHtml . '</div>';
        $output[] = $indentation . '      <div class="media-links">';

        if ($section->contentHtml) {
            $output[] = $indentation . '        <a href="#" class="js-media-read-more" data-media-id="' . $section->id . '">' . $localisation->getValue('albumPublicReadMore') . '<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>';
        }

        if (count($section->media) > 1) {
            $output[] = $indentation . '        <a href="#" class="js-media-view" data-media-id="' . $section->id . '">' . $localisation->getValue('albumPublicMorePictures') . '<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>';
        }

        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="media-content-navigation">';
        $output[] = $indentation . '      <div class="js-navigation-left"></div>';
        $output[] = $indentation . '      <div class="js-navigation-right"></div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="media-text-panel null" id="media-text-panel-' . $section->id . '">';
        $output[] = $indentation . '      <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>';
        $output[] = $indentation . '      <div class="media-panel-content">';

        if ($section->mainMedia) {
            $output[] = $indentation . '      <img loading="lazy" src="' . $section->mainMedia->getPathWithNameSmall() . '" alt="' . $section->mainMedia->buildAltText() . '">';
        }

        $output[] = $indentation . '      <p>' . $section->contentHtml . '</p>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
