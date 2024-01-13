<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Model\AlbumSectionMedia;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\LocalisationUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

final class AlbumHtmlGenerator
{
    public static function generateAlbumStatusHtml(
        AlbumStatus $status,
        LocalisationUtil $localisationUtil,
    ): string {
        return '<span class="article-status m-t-0 '
            . $status->getClass() . '">'
            . $status->getIcon()
            . $localisationUtil->getValue('articleStatus' . $status->name)
            . '</span>' . PHP_EOL;
    }

    public static function generateDynamicAlbumStatusHtml(
        AlbumStatus $albumStatus,
        LocalisationUtil $localisationUtil,
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
            $statusName = $localisationUtil->getValue('articleStatus' . $item->name);
            $output[] = $indentation . '    <li><a data-checked="' . ($albumStatus === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option album-status-dd-option ' . $statusClassname . '" href="#">' . $icon . $statusName . '</a></li>';
        }

        $icon = $albumStatus->getIcon();
        $selectedStatusClassname = $albumStatus->getClass();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="album-status-dd-label" for="album-status-dd-checkbox" data-status-id="' . $albumStatus->value . '" class="dropdown-menu-label ' . $selectedStatusClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $localisationUtil->getValue('articleStatus' . $albumStatus->name) . '</span>';
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

        $localisationUtil = Core::getLocalisationUtil($responseData->siteLanguage);

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
        $output[] = $indentation . '      ' . self::generateAlbumStatusHtml($album->status, $localisationUtil);
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumSectionHtml(
        AlbumSection $section,
        LocalisationUtil $localisationUtil,
        string $indentation = '',
    ): string {
        $lazyLoading = $section->sequence > 1;

        $output = [];
        $output[] = $indentation . '<div class="album-section-item" data-album-section-id="' . $section->id . '" data-sequence="' . $section->sequence . '">';
        $output[] = $indentation . '  <div class="album-section-item-content">';
        $output[] = $indentation . '    <div class="drop-loading null"><img src="/img/loading.gif" class="img-svg img-svg-40" width="40" height="40" alt="' . $localisationUtil->getValue('globalSaving') . '"></div>';

        $output[] = $indentation . '    <p class="section-label null">' . $localisationUtil->getValue('globalTitle') . '</p>';
        $output[] = $indentation . '    <h3 class="section-title-html" data-before="' . $section->titleHtml . '">' . ($section->titleHtml ?: '-') . '</h3>';
        $output[] = $indentation . '    <p class="section-label null">' . $localisationUtil->getValue('globalSubtitle') . '</p>';
        $output[] = $indentation . '    <div class="section-subtitle-html" data-before="' . $section->subtitleHtml . '">' . ($section->subtitleHtml ?: '-') . '</div>';
        $output[] = $indentation . '    <p class="section-label null">' . $localisationUtil->getValue('globalContent') . '</p>';
        $output[] = $indentation . '    <div class="section-content-html section-content-html-' . $section->id . '" data-before="' . $section->contentHtml . '">' . ($section->contentHtml ?: '-') . '</div>';

        $output[] = $indentation . '    <div id="album-section-main-media-' . $section->id . '" class="main-image-container" data-before="' . $section->mainMedia?->id . '">';
        if ($section->mainMedia) {
            $output[] = $indentation . '      <img class="album-section-main-media" data-media-id="' . $section->mainMedia->id . '" src="' . $section->mainMedia->getPathWithNameSmall() . '" alt="' . $section->mainMedia->buildAltText() . '"' . ($lazyLoading ? ' loading="lazy"' : '') . '>';
        }

        $output[] = $indentation . '      <div class="main-image-button-container null">';
        $output[] = $indentation . '        <a href="#" class="main-image-button main-image-button-red album-section-main-media-delete-js' . ($section->mainMedia ? '' : ' null') . '" data-media-id="' . $section->mainMedia?->id . '" data-event-listener-action="albumSectionDeleteMainMedia" data-target-container-id="album-section-main-media-' . $section->id . '">';
        $output[] = $indentation . '          <img class="img-svg" src="/img/svg/trash-white.svg" alt="' . $localisationUtil->getValue('globalRemoveImage') . '" title="' . $localisationUtil->getValue('globalRemoveImage') . '">';
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '        <a href="#" class="main-image-button album-section-main-media-js" data-section-id="' . $section->id . '" data-media-id="' . $section->mainMedia?->id . '" data-event-listener-action="albumSectionSelectMainMedia" data-target-container-id="album-section-main-media-' . $section->id . '">';
        $output[] = $indentation . '          <img class="img-svg" src="/img/svg/image.svg" alt="' . $localisationUtil->getValue('globalSelectImage') . '" title="' . $localisationUtil->getValue('globalSelectImage') . '">';
        $output[] = $indentation . '          <span>' . ($section->mainMedia ? $localisationUtil->getValue('globalModify') : $localisationUtil->getValue('globalSelectImage')) . '</span>';
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="m-b-1">';
        $output[] = $indentation . '      <a href="#" class="album-section-edit-js" data-album-section-id="' . $section->id . '">' . $localisationUtil->getValue('globalEdit') . '</a>';
        $output[] = $indentation . '      <p class="section-label null">' . $localisationUtil->getValue('globalSequence') . '</p>';
        $output[] = $indentation . '      <div><span class="section-sequence" data-before="' . $section->sequence . '">#' . $section->sequence . '</span></div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="album-section-item-edit-container">';
        $output[] = $indentation . '      <div class="album-section-button-container-js null">';
        $output[] = $indentation . '        <button class="album-section-button album-section-cancel-js" data-album-section-id="' . $section->id . '">' . $localisationUtil->getValue('globalCancel') . '</button>';
        $output[] = $indentation . '        <button class="album-section-button is-success album-section-save-js" data-album-section-id="' . $section->id . '">' . $localisationUtil->getValue('globalSave') . '</button>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="album-section-item-media-wrapper">';
        $output[] = $indentation . '    <div class="album-section-item-media-header">';
        $output[] = $indentation . '      <span class="count">' . count($section->media) . '</span> ' . $localisationUtil->getValue('globalImages');
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '    <div id="album-section-item-media-' . $section->id . '" class="album-section-item-media" data-album-section-id="' . $section->id . '">';

        /** @var AlbumSectionMedia $sectionMedia */
        foreach ($section->media as $sectionMedia) {
            $output[] = self::generateAlbumSectionMediaHtml($sectionMedia, $localisationUtil, $indentation . '      ', $lazyLoading);
        }

        $output[] = $indentation . '      <a href="#" class="button select-media-action button-media-add" data-target-container-id="album-section-item-media-' . $section->id .'" data-event-listener-action="albumSectionAddMedia">';
        $output[] = $indentation . '        <img class="img-svg img-svg-30" width="30" height="30" src="/img/svg/image.svg" alt="Image">';
        $output[] = $indentation . '        <span>' . $localisationUtil->getValue('globalAdd') . '</span>';
        $output[] = $indentation . '      </a>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumSectionMediaHtml(
        AlbumSectionMedia $albumSectionMedia,
        LocalisationUtil $localisationUtil,
        string $indentation = '',
        bool $lazyLoading = false,
    ): string {
        $titleAlt = $albumSectionMedia->media->buildAltText();
        $lazyLoadingString = $lazyLoading ? ' loading="lazy"' : '';

        $output = [];
        $output[] = $indentation . '<div class="album-section-media-container item-draggable">';
        $output[] = $indentation . '  <figure>';
        $output[] = $indentation . '    <img id="album-section-media-' . $albumSectionMedia->id . '" src="' . $albumSectionMedia->media->getPathWithNameSmall() . '" class="media-item" alt="' . $titleAlt . '" title="' . $titleAlt . '" data-media-id="' . $albumSectionMedia->media->id . '" data-sequence="' . $albumSectionMedia->sequence . '" data-album-section-media-id=' . $albumSectionMedia->id . ' draggable="true">';
        $output[] = $indentation . '    <div class="album-section-media-options">';
        $output[] = $indentation . '      <div class="media-caption album-section-media-caption-js" data-media-id="' . $albumSectionMedia->media->id . '" data-album-section-id="' . $albumSectionMedia->albumSectionId . '" data-album-section-media-id="' . $albumSectionMedia->id . '">' . ($albumSectionMedia->captionHtml ?: '-') . '</div>';
        $output[] = $indentation . '      <img class="img-svg album-section-image-delete album-section-media-delete-js" src="/img/svg/trash-white.svg" alt="' . $localisationUtil->getValue('globalRemoveImage') . '" title="' . $localisationUtil->getValue('globalRemoveImage') . '"' . $lazyLoadingString . ' data-album-section-media-id="' . $albumSectionMedia->id . '" data-media-id="' . $albumSectionMedia->media->id . '" data-event-listener-action="albumSectionDeleteMedia" data-target-container-id="album-section-item-media-' . $albumSectionMedia->albumSectionId . '">';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </figure>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output);
    }

    public static function generatePublicAlbumSectionMediaArray(
        array $mediaArray,
        int $sectionSequence,
        string $indentation = '',
        ?string $cssClass = 'media',
    ): array {
        $output = [];

        $count = 0;
        /** @var AlbumSectionMedia $albumSectionMedia */
        foreach ($mediaArray as $albumSectionMedia) {
            $class = $cssClass . ($count === 0 ? '-active' : '-hidden');
            $lazyLoading = $count === 0 && $sectionSequence === 0 ? '' : ' loading="lazy"';
            $output[] = $indentation . '    <img src="' . $albumSectionMedia->media->getPathWithNameMedium() . '" class="media-item ' . $class . '" data-caption="' . $albumSectionMedia->captionHtml . '" data-sequence="' . $albumSectionMedia->sequence . '" alt="' . $albumSectionMedia->buildAltText() . '"' . $lazyLoading . '>';
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
                sectionSequence: $section->sequence,
                indentation: '    ',
                cssClass: 'media-opacity',
            )
        );

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="media-content-wrapper">';

        $output[] = $indentation . '    <div class="content-main-header">';
        $output[] = $indentation . '      <div class="content-main-header-left">';
        $output[] = $indentation . '        <h1 class="media-title main-slide-title">' . $section->titleHtml . '</h1>';
        $output[] = $indentation . '        <div class="media-text">' . $section->contentHtml . '</div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="dots-nine-wrapper">';
        $output[] = $indentation . '        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumTemplateNewYorkSectionHtml(
        AlbumSection $section,
        LocalisationUtil $localisationUtil,
        string $indentation = '',
    ): string {
        if (!$section->media) {
            return '';
        }

        $mediaCaptionForFirstMedia = null;
        $maxMediaSequence = 0;
        /** @var AlbumSectionMedia $media */
        foreach ($section->media as $media) {
            if (!$mediaCaptionForFirstMedia) {
                $mediaCaptionForFirstMedia = $media->captionHtml;
            }

            if ($media->sequence > $maxMediaSequence) {
                $maxMediaSequence = $media->sequence;
            }
        }

        $output = [];
        $output[] = $indentation . '<section class="content-child js-content-slider" data-section-id="' . $section->id . '">';
        $output[] = $indentation . '  <div class="media-wrapper">';

        $output = array_merge(
            $output,
            self::generatePublicAlbumSectionMediaArray(
                mediaArray: $section->media,
                sectionSequence: $section->sequence,
                indentation: '    ',
            )
        );

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="media-content-wrapper">';
        $output[] = $indentation . '    <div class="content-header">';

        $output[] = $indentation . '      <div class="content-header-left">';
        $output[] = $indentation . '        <span class="number">' . $section->sequence . '</span>';
        $output[] = $indentation . '        <div>';
        $output[] = $indentation . '          <h1 class="media-title">' . $section->titleHtml . '</h1>';
        if ($section->subtitleHtml) {
            $output[] = $indentation . '          <p class="media-subtitle">' . $section->subtitleHtml . '</p>';
        }
        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="dots-nine-wrapper">';
        $output[] = $indentation . '        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="content-text-wrapper">';
        $output[] = $indentation . '      <div class="content-text">';
        $output[] = $indentation . '        <div class="media-text">' . StringUtil::getFirstParagraphAsPlainText($section->contentHtml) . '</div>';
        $output[] = $indentation . '        <div class="media-links">';

        $output[] = $indentation . (
            $section->contentHtml
                ? '          <a href="#" class="js-media-read-more" data-section-id="' . $section->id . '">' . $localisationUtil->getValue('albumPublicReadMore') . '<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>'
                : '<span></span>'
            );

        $output[] = $indentation . (
            count($section->media) > 1
                ? '          <a href="#" class="js-media-view">' . $localisationUtil->getValue('albumPublicMorePictures') . '<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>'
                : '<span></span>'
            );

        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="media-info">';
        $output[] = $indentation . '        <div class="media-info-inner">';
        $output[] = $indentation . '          <img src="/img/svg/article-white.svg" class="js-media-read-more js-media-read-more-icon null" alt="Ler máis" width="20" height="20" data-section-id="' . $section->id . '">';
        $output[] = $indentation . '          <div><span class="media-sequence">1</span> ' . $localisationUtil->getValue('globalOf') . ' ' . $maxMediaSequence . '</div>';
        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '        <div class="media-caption-html">' . $mediaCaptionForFirstMedia . '</div>';
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

        $output[] = $indentation . '        <div>' . $section->contentHtml . '</div>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumTemplateNewYorkModalSectionsHtml(
        LocalisationUtil $localisationUtil,
        array $sections,
        string $indentation = '',
    ): string {
        $output = [];
        $output[] = $indentation . '<div class="album-new-york-sections-modal-js modal-wrapper">';
        $output[] = $indentation . '  <a href="#" class="modal-close-button">';
        $output[] = $indentation . '    <img src="/img/svg/x-white.svg" class="img-svg" width="20" height="20" alt="' . $localisationUtil->getValue('globalClose') . '">';
        $output[] = $indentation . '  </a>';

        $count = 0;
        /** @var AlbumSection $section */
        foreach ($sections as $section) {
            if ($section->sequence === 0 || !$section->media) {
                continue;
            }

            $text = $section->titleHtml . ($section->subtitleHtml ? ', ' . $section->subtitleHtml : '');
            $output[] = $indentation . '  <div class="modal-item" data-section-id="' . $section->id . '">';

            /** @var Media $sectionMedia */
            $sectionMedia = $section->mainMedia ?? $section->media[0]?->media ?? null;
            if ($sectionMedia) {
                $output[] = $indentation . '    <img src="' . $sectionMedia->getPathWithNameSmall() . '" class="modal-item-thumb" alt="' . $sectionMedia->buildAltText() . '">';
            }

            $output[] = $indentation . '    <a href="#" class="js-section-item" data-section-id="' . $section->id . '">';
            $output[] = $indentation . '      <span class="number">' . $section->sequence . '</span>';
            $output[] = $indentation . '      <span>' . $text . '</span>';
            $output[] = $indentation . '    </a>';
            $output[] = $indentation . '  </div>';
            $count++;
        }

        for ($i = 0 ;$i<=round($count / 4); $i++) {
            $output[] = $indentation . '  <div class="modal-item hidden"></div>';
        }

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
