<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

?>
  <main>
<?=$this->insert('partials/articles/header', ['responseData' => $responseData])?>
<?=$this->insert('partials/articles/filter', ['responseData' => $responseData])?>
    <div class="m-t-1 m-r-1 m-b-1 m-l-1">
      <div class="table">
        <div class="table-row header">
          <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalTitle')?></div>
        </div>
<?php
    /** @var Article $article */
    foreach ($responseData->articles as $article) {
        echo ArticleHtmlGenerator::generateArticleRowHtml($responseData, $article);
    }
?>
      </div>
    </div>
  </main>
