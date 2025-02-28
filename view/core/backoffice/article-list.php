<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

?>
  <main>
<?=$this->insert('partials/article/header', ['responseData' => $responseData])?>
    <div class="backoffice-wrapper">
<?=$this->insert('partials/article/filter', ['responseData' => $responseData])?>
      <div class="table">
<?php
    /** @var Article $article */
    foreach ($responseData->articles as $article) {
        echo ArticleHtmlGenerator::generateArticleRowHtml($responseData, $article);
    }
?>
      </div>
    </div>
  </main>
