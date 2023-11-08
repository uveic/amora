<?php

use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

?>
  <main>
<?=$this->insert('partials/albums/header', ['responseData' => $responseData])?>
<?=$this->insert('partials/albums/filter', ['responseData' => $responseData])?>
    <div class="backoffice-wrapper">
      <div class="album-wrapper">
<?php
    /** @var Album $album */
    foreach ($responseData->albums as $album) {
        echo AlbumHtmlGenerator::generateAlbumRowHtml(
            responseData: $responseData,
            album: $album,
            indentation: '        ',
        );
    }
?>
      </div>
    </div>
  </main>
