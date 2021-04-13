<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="page-header">
      <h1><?=$responseData->getLocalValue('navAdminArticles')?></h1>
      <div class="links">
        <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/new" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
      </div>
    </section>
    <section class="content-flex-block">
      <div class="table">
        <div class="table-row header">
          <div class="table-item edit flex-no-grow"></div>
          <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalTitle')?></div>
          <div class="table-item"><?=$responseData->getLocalValue('globalTags')?></div>
        </div>
<?php
/** @var Article $article */
foreach ($responseData->getArticles() as $article) {
    $articleTitleHtml = ArticleEditHtmlGenerator::generateArticleTitleHtml($responseData, $article);
?>
            <div class="table-row">
              <div class="table-item edit flex-no-grow"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/<?=$article->getId()?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
              <div class="table-item flex-grow-2"><?=$articleTitleHtml?></div>
              <div class="table-item"><?=$article->getTagsAsString()?></div>
            </div>
<?php } ?>
      </div>
    </section>
  </main>
