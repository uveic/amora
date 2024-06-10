<?php

use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;

/** @var HtmlResponseData $responseData */

if (!$responseData->album) {
  return;
}

/** @var Album $album */
$album = $responseData->album;

$localisationUtil = Core::getLocalisationUtil($album->language);

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('../../../../app/frontend/public/partials/head', ['responseData' => $responseData])?>
  <link href="/css/album/new-york.css?v=000" rel="stylesheet" type="text/css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%2210 0 100 100%22><text y=%22.90em%22 font-size=%2290%22>🚆</text></svg>">
</head>
<body>
<?php
  echo AlbumHtmlGenerator::generateAlbumTemplateNewYorkModalSectionsHtml(
        localisationUtil: $localisationUtil,
        album: $album,
        indentation: '    ',
    ) . PHP_EOL;
?>
  <main class="content-main">
<?php
    echo AlbumHtmlGenerator::generateAlbumTemplateNewYorkFirstSectionHtml(
        section: $album->sections[0] ?? null,
        indentation: '    ',
    ) . PHP_EOL;

    /** @var AlbumSection $section */
    foreach ($album->sections as $key => $section) {
        if ($key === 0) {
            continue;
        }

        echo AlbumHtmlGenerator::generateAlbumTemplateNewYorkSectionHtml(
            section: $section,
            localisationUtil: $localisationUtil,
            indentation: '    ',
        ) . PHP_EOL;
    }
?>
  </main>
  <script type="module" src="/js/album/new-york.js?v=000"></script>
</body>

</html>
