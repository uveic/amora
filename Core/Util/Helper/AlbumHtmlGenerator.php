<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\Collection;
use Amora\Core\Module\Album\Model\CollectionMedia;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\LocalisationUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

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
            $output[] = $indentation . '    <li><a data-checked="' . ($albumStatus === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option album-status-dd-option ' . $statusClassname . '" href="#" data-dropdown-identifier="album-status">' . $icon . $statusName . '</a></li>';
        }

        $icon = $albumStatus->getIcon();
        $selectedStatusClassname = $albumStatus->getClass();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="album-status-dd-label" for="album-status-dd-checkbox" data-status-id="' . $albumStatus->value . '" class="dropdown-menu-label ' . $selectedStatusClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $localisationUtil->getValue('articleStatus' . $albumStatus->name) . '</span>';
        $output[] = $indentation . '    ' . CoreIcons::CARET_DOWN;
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
            ? '<a href="' . $albumPublicUrl . '">' . CoreIcons::ARROW_SQUARE_OUT . '</a>'
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

    public static function generateCollectionHtml(
        Collection $collection,
        LocalisationUtil $localisationUtil,
        string $indentation = '',
    ): string {
        $lazyLoading = $collection->sequence > 1;

        $output = [];
        $output[] = $indentation . '<div class="collection-item" data-collection-id="' . $collection->id . '" data-sequence="' . $collection->sequence . '">';
        $output[] = $indentation . '  <div class="collection-item-content">';

        $output[] = $indentation . '    <p class="collection-label null">' . $localisationUtil->getValue('globalTitle') . '</p>';
        $output[] = $indentation . '    <h3 class="collection-title-html" data-before="' . $collection->titleHtml . '">' . ($collection->titleHtml ?: '-') . '</h3>';
        $output[] = $indentation . '    <p class="collection-label null">' . $localisationUtil->getValue('globalSubtitle') . '</p>';
        $output[] = $indentation . '    <div class="collection-subtitle-html" data-before="' . $collection->subtitleHtml . '">' . ($collection->subtitleHtml ?: '-') . '</div>';
        $output[] = $indentation . '    <p class="collection-label null">' . $localisationUtil->getValue('globalContent') . '</p>';
        $output[] = $indentation . '    <div class="collection-content-html-before null">' . ($collection->contentHtml ?: '') . '</div>';
        $output[] = $indentation . '    <div class="collection-content-html collection-content-html-' . $collection->id . '">' . ($collection->contentHtml ?: '-') . '</div>';

        $output[] = $indentation . '    <div id="collection-main-media-' . $collection->id . '" class="main-image-container" data-before="' . $collection->mainMedia?->id . '">';
        if ($collection->mainMedia) {
            $output[] = $indentation . '      <img class="media-item" data-media-id="' . $collection->mainMedia->id . '" src="' . $collection->mainMedia->getPathWithNameSmall() . '" alt="' . $collection->mainMedia->buildAltText() . '"' . ($lazyLoading ? ' loading="lazy"' : '') . '>';
        }

        $output[] = $indentation . '      <div class="main-image-button-container null">';
        $output[] = $indentation . '        <a href="#" class="main-image-button main-image-button-red generic-media-delete-js' . ($collection->mainMedia ? '' : ' null') . '" data-media-id="' . $collection->mainMedia?->id . '" data-event-listener-action="handleGenericMediaDeleteClick" data-target-container-id="collection-main-media-' . $collection->id . '">';
        $output[] = $indentation . '          ' . CoreIcons::TRASH;
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '        <a href="#" class="main-image-button select-media-action" data-collection-id="' . $collection->id . '" data-media-id="' . $collection->mainMedia?->id . '" data-event-listener-action="handleGenericMainMediaClick" data-target-container-id="collection-main-media-' . $collection->id . '">';
        $output[] = $indentation . '          ' . CoreIcons::IMAGE;
        $output[] = $indentation . '          <span>' . ($collection->mainMedia ? $localisationUtil->getValue('globalModify') : $localisationUtil->getValue('globalSelectImage')) . '</span>';
        $output[] = $indentation . '        </a>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="m-b-1">';
        $output[] = $indentation . '      <a href="#" class="collection-edit-js" data-collection-id="' . $collection->id . '">' . $localisationUtil->getValue('globalEdit') . '</a>';
        $output[] = $indentation . '      <p class="collection-label null">' . $localisationUtil->getValue('globalSequence') . '</p>';
        $output[] = $indentation . '      <div><span class="collection-sequence" data-before="' . $collection->sequence . '">#' . $collection->sequence . '</span></div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="collection-item-edit-container">';
        $output[] = $indentation . '      <div class="collection-button-container-js null">';
        $output[] = $indentation . '        <button class="collection-button collection-cancel-js" data-collection-id="' . $collection->id . '">' . $localisationUtil->getValue('globalCancel') . '</button>';
        $output[] = $indentation . '        <button class="collection-button is-success collection-save-js" data-collection-id="' . $collection->id . '">' . $localisationUtil->getValue('globalSave') . '</button>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="collection-item-media-wrapper">';
        $output[] = $indentation . '    <div class="collection-item-media-header">';
        $output[] = $indentation . '      <span class="count">' . count($collection->media) . '</span> ' . $localisationUtil->getValue('globalImages');
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '    <div id="collection-item-media-' . $collection->id . '" class="collection-item-media" data-collection-id="' . $collection->id . '">';

        /** @var CollectionMedia $collectionMedia */
        foreach ($collection->media as $collectionMedia) {
            $output[] = self::generateCollectionMediaHtml($collectionMedia, $indentation . '      ', $lazyLoading);
        }

        $output[] = $indentation . '      <a href="#" class="button select-media-action button-media-add" data-type-id="' . MediaType::Image->value . '" data-target-container-id="collection-item-media-' . $collection->id .'" data-event-listener-action="collectionAddMedia">';
        $output[] = $indentation . '        ' . CoreIcons::IMAGE;
        $output[] = $indentation . '        <span>' . $localisationUtil->getValue('globalAdd') . '</span>';
        $output[] = $indentation . '      </a>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateCollectionMediaHtml(
        CollectionMedia $collectionMedia,
        bool $lazyLoading = false,
        string $bottomHtml = '',
        string $indentation = '',
    ): string {
        $titleAlt = $collectionMedia->media->buildAltText();
        $lazyLoadingString = $lazyLoading ? ' loading="lazy"' : '';

        $output = [];
        $output[] = $indentation . '<div class="collection-media-container item-draggable" data-media-id="' . $collectionMedia->media->id . '">';
        $output[] = $indentation . '  <figure>';
        $output[] = $indentation . '    <img id="collection-media-' . $collectionMedia->id . '" src="' . $collectionMedia->media->getPathWithNameSmall() . '" class="media-item" alt="' . $titleAlt . '" title="' . $titleAlt . '"' . $lazyLoadingString . ' data-media-id="' . $collectionMedia->media->id . '" data-sequence="' . $collectionMedia->sequence . '" data-collection-media-id=' . $collectionMedia->id . ' draggable="true">';
        $output[] = $indentation . '    <div class="collection-media-options">';
        $output[] = $indentation . '      <div class="media-caption collection-media-caption-js" data-media-id="' . $collectionMedia->media->id . '" data-collection-id="' . $collectionMedia->collectionId . '" data-collection-media-id="' . $collectionMedia->id . '">' . ($collectionMedia->captionHtml ?: '-') . '</div>';
        $output[] = $indentation . '      <span class="collection-media-delete-js" data-collection-media-id="' . $collectionMedia->id . '" data-media-id="' . $collectionMedia->media->id . '" data-event-listener-action="collectionDeleteMedia" data-target-container-id="collection-item-media-' . $collectionMedia->collectionId . '">' . CoreIcons::TRASH . '</span>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </figure>';

        if ($bottomHtml) {
            $output[] = $indentation . '  <div class="collection-media-info">' . $bottomHtml . '</div>';
        }

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output);
    }

    public static function generatePublicCollectionMediaArray(
        array $mediaArray,
        int $collectionSequence,
        string $indentation = '',
        ?string $cssClass = 'media',
    ): array {
        $output = [];

        $count = 0;
        /** @var CollectionMedia $collectionMedia */
        foreach ($mediaArray as $collectionMedia) {
            $class = $cssClass . ($count === 0 ? '-active' : '-hidden');
            $lazyLoading = $count === 0 && $collectionSequence === 0 ? '' : ' loading="lazy"';
            $output[] = $indentation . '    <img src="' . $collectionMedia->media->getPathWithNameMedium() . '" class="media-item ' . $class . '" data-caption="' . $collectionMedia->captionHtml . '" data-sequence="' . $collectionMedia->sequence . '" alt="' . $collectionMedia->buildAltText() . '"' . $lazyLoading . '>';
            $count++;
        }

        return $output;
    }

    public static function generateAlbumTemplateNewYorkFirstCollectionHtml(
        ?Collection $collection,
        string $indentation = '',
    ): string {
        if (!$collection || !$collection->media) {
            return '';
        }

        $output = [];
        $output[] = $indentation . '<section class="content-child js-content-slider-fade js-content-first">';
        $output[] = $indentation . '  <div class="media-wrapper">';

        $output = array_merge(
            $output,
            self::generatePublicCollectionMediaArray(
                mediaArray: $collection->media,
                collectionSequence: $collection->sequence,
                indentation: '    ',
                cssClass: 'media-opacity',
            )
        );

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="media-content-shadow"></div>';

        $output[] = $indentation . '  <div class="media-content-wrapper">';

        $output[] = $indentation . '    <div class="content-main-header">';
        $output[] = $indentation . '      <div class="content-main-header-left">';
        $output[] = $indentation . '        <h1 class="media-title main-slide-title">' . $collection->titleHtml . '</h1>';
        $output[] = $indentation . '        <div class="media-text">' . $collection->contentHtml . '</div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="dots-nine-wrapper">';
        $output[] = $indentation . '        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumTemplateNewYorkCollectionHtml(
        Collection $collection,
        LocalisationUtil $localisationUtil,
        string $indentation = '',
    ): string {
        if (!$collection->media) {
            return '';
        }

        $mediaCaptionForFirstMedia = null;
        $maxMediaSequence = 0;
        /** @var CollectionMedia $media */
        foreach ($collection->media as $media) {
            if (!$mediaCaptionForFirstMedia) {
                $mediaCaptionForFirstMedia = $media->captionHtml;
            }

            if ($media->sequence > $maxMediaSequence) {
                $maxMediaSequence = $media->sequence;
            }
        }

        $output = [];
        $output[] = $indentation . '<section id="' . $collection->buildUniqueSlug() . '" class="content-child js-content-slider" data-collection-id="' . $collection->id . '">';
        $output[] = $indentation . '  <div class="media-wrapper">';

        $output = array_merge(
            $output,
            self::generatePublicCollectionMediaArray(
                mediaArray: $collection->media,
                collectionSequence: $collection->sequence,
                indentation: '    ',
            )
        );

        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="media-content-shadow"></div>';

        $output[] = $indentation . '  <div class="media-content-wrapper">';
        $output[] = $indentation . '    <div class="content-header">';

        $output[] = $indentation . '      <div class="content-header-left">';
        $output[] = $indentation . '        <span class="number">' . $collection->sequence . '</span>';
        $output[] = $indentation . '        <div>';
        $output[] = $indentation . '          <h1 class="media-title">' . $collection->titleHtml . '</h1>';
        if ($collection->subtitleHtml) {
            $output[] = $indentation . '          <p class="media-subtitle">' . $collection->subtitleHtml . '</p>';
        }
        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="dots-nine-wrapper">';
        $output[] = $indentation . '        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="content-text-wrapper">';
        $output[] = $indentation . '      <div class="content-text">';

        $mediaText = StringUtil::getFirstParagraphAsPlainText(
            text: $collection->contentHtml,
            maxLength: 200,
            trimToFirstSentence: true,
        );
        $output[] = $indentation . '        <div class="media-text">' . $mediaText . '</div>';
        $output[] = $indentation . '        <div class="media-links">';

        $output[] = $indentation . (
            $collection->contentHtml
                ? '          <a href="#" class="js-media-read-more" data-collection-id="' . $collection->id . '">' . $localisationUtil->getValue('albumPublicReadMore') . CoreIcons::ARTICLE . '</a>'
                : '<span></span>'
            );

        $output[] = $indentation . (
            count($collection->media) > 1
                ? '          <a href="#" class="js-media-view">' . $localisationUtil->getValue('albumPublicMorePictures') . CoreIcons::ARROW_RIGHT . '</a>'
                : '<span></span>'
            );

        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '      </div>';

        $output[] = $indentation . '      <div class="media-info">';
        $output[] = $indentation . '        <div class="media-info-inner">';
        $output[] = $indentation . '          <span class="js-media-read-more js-media-read-more-icon null" data-collection-id="' . $collection->id . '">' . CoreIcons::ARTICLE . '</span>';
        $output[] = $indentation . '          <div><span class="media-sequence">1</span> ' . $localisationUtil->getValue('globalOf') . ' ' . $maxMediaSequence . '</div>';
        $output[] = $indentation . '        </div>';
        $output[] = $indentation . '        <div class="media-caption-html">' . $mediaCaptionForFirstMedia . '</div>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="media-content-navigation">';
        $output[] = $indentation . '      <div class="js-navigation-left"></div>';
        $output[] = $indentation . '      <div class="js-navigation-right"></div>';
        $output[] = $indentation . '    </div>';

        $output[] = $indentation . '    <div class="media-text-panel null" id="media-text-panel-' . $collection->id . '">';
        $output[] = $indentation . '      <a class="media-panel-close" href="#">' . CoreIcons::CLOSE . '</a>';
        $output[] = $indentation . '      <div class="media-panel-content">';

        if ($collection->mainMedia) {
            $output[] = $indentation . '      <img loading="lazy" src="' . $collection->mainMedia->getPathWithNameSmall() . '" alt="' . $collection->mainMedia->buildAltText() . '">';
        }

        $output[] = $indentation . '        <div>' . $collection->contentHtml . '</div>';
        $output[] = $indentation . '      </div>';
        $output[] = $indentation . '    </div>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</section>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumTemplateNewYorkModalCollectionsHtml(
        Album $album,
        string $indentation = '',
    ): string {
        $output = [];
        $output[] = $indentation . '<div class="album-new-york-collections-modal-js modal-wrapper">';
        $output[] = $indentation . '  <a href="#" class="modal-close-button">' . CoreIcons::CLOSE . '  </a>';

        $output[] = $indentation . '  <div class="album-modal-header">';
        $output[] = $indentation . '    <h1 class="album-modal-title">' . $album->titleHtml . '</h1>';

        $output[] = $indentation . '    <div class="media-text album-modal-text">';

        if ($album->mainMedia) {
            $output[] = $indentation . '    <img src="' . $album->mainMedia->getPathWithNameMedium() . '" alt="' . $album->mainMedia->buildAltText() . '" >';
        }

        $output[] = $album->contentHtml . '</div>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '  <div class="collections-wrapper">';

        $count = 0;
        /** @var Collection $collection */
        foreach ($album->collections as $collection) {
            if ($collection->sequence === 0 || !$collection->media) {
                continue;
            }

            $text = $collection->titleHtml . ($collection->subtitleHtml ? ', ' . $collection->subtitleHtml : '');
            $output[] = $indentation . '    <div class="modal-item" data-collection-id="' . $collection->id . '">';

            /** @var Media $collectionMedia */
            $collectionMedia = $collection->mainMedia ?? $collection->media[0]?->media ?? null;
            if ($collectionMedia) {
                $output[] = $indentation . '      <img src="' . $collectionMedia->getPathWithNameSmall() . '" class="modal-item-thumb" alt="' . $collectionMedia->buildAltText() . '" loading="lazy">';
            }

            $output[] = $indentation . '      <a href="#" class="js-collection-item" data-collection-id="' . $collection->id . '">';
            $output[] = $indentation . '        <span class="number">' . $collection->sequence . '</span>';
            $output[] = $indentation . '        <span>' . $text . '</span>';
            $output[] = $indentation . '      </a>';
            $output[] = $indentation . '    </div>';
            $count++;
        }

        $hiddenItemCount = round($count / 4);
        if ($hiddenItemCount > 7) {
            $hiddenItemCount = 7;
        }

        for ($i = 0 ;$i <= $hiddenItemCount; $i++) {
            $output[] = $indentation . '  <div class="modal-item hidden"></div>';
        }

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
