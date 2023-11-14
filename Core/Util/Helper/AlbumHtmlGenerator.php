<?php

namespace Amora\Core\Util\Helper;

use Amora\App\Value\Language;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Util\UrlBuilderUtil;

final class AlbumHtmlGenerator
{
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
    ): string
    {
        $albumEditUrl = UrlBuilderUtil::buildBackofficeAlbumViewUrl(
            language: $responseData->siteLanguage,
            albumId: $album->id,
        );

        $albumPublicUrl = UrlBuilderUtil::buildPublicAlbumUrl(
            slug: $album->slug->slug,
            language: $responseData->siteLanguage,
        );

        $albumPublicLinkHtml = $album->status->isPublic()
            ? '<a href="' . $albumPublicUrl . '"><img src="/img/svg/arrow-square-out.svg" class="img-svg m-l-05" alt="Public link" width="20" height="20"></a>'
            : '';

        $output = [];
        $output[] = $indentation . '<div class="album-item">';
        $output[] = $indentation . '  <div class="album-image">';
        $output[] = $indentation . '    <a href="' . $albumEditUrl . '"><img src="' . $album->mainMedia->getPathWithNameSmall() . '" alt="' . $album->mainMedia->buildAltText() . '"></a>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="album-content">';
        $output[] = $indentation . '    <a href="' . $albumEditUrl . '" class="album-title">' . $album->titleHtml . '</a>';
        $output[] = $indentation . '    ' . $albumPublicLinkHtml;
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateAlbumSectionHtml(
        AlbumSection $section,
        string $indentation = '',
    ): string
    {
        $output = [];
        $output[] = $indentation . '<div class="album-section-item" data-album-section-id="' . $section->id . '">';
        $output[] = $indentation . '  <div class="album-section-item-content">';
        $output[] = $indentation . '    <h3>' . $section->titleHtml . '</h3>';
        $output[] = $indentation . '    <p>' . $section->contentHtml;
        $output[] = $indentation . '    <img src="' . $section->mainMedia->getPathWithNameSmall() . '" alt="' . $section->mainMedia->buildAltText() . '">';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="album-section-item-media">';
        $output[] = $indentation . '    <a href="#" class="button is-success select-media-action" data-album-section-id="' . $section->id .'" data-event-listener-action="albumSectionAddMedia">';
        $output[] = $indentation . '      <img class="img-svg img-svg-30" width="30" height="30" src="/img/svg/image-white.svg" alt="Img">';
        $output[] = $indentation . '      <span>Engadir</span>';
        $output[] = $indentation . '    </a>';
        $output[] = $indentation . '  </div>';

        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
