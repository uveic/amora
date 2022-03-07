<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

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
    $articleItemHtml = ArticleEditHtmlGenerator::generateArticleTitleHtml($responseData, $article);
?>
            <div class="table-row">
              <div class="table-item flex-grow-2" style="align-items: flex-start;"><?=$articleItemHtml?></div>
              <div class="table-item edit flex-no-grow" style="justify-content: flex-end;"><a href="<?=UrlBuilderUtil::buildBackofficeArticleUrl($responseData->siteLanguage, $article->id)?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
            </div>
<?php } ?>
      </div>
    </div>
  </main>
