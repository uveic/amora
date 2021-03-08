<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleStatus;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="content">
      <div class="form-header m-r-1 m-l-1">
        <h1><?=$responseData->getLocalValue('navAdminArticles')?></h1>
        <div class="links">
          <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/new" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
        </div>
      </div>
      <section class="content-flex-block">
        <div class="table">
          <div class="table-row header">
            <div class="table-item edit flex-no-grow"></div>
            <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalTitle')?></div>
            <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalTags')?></div>
            <div class="table-item width-1"><?=$responseData->getLocalValue('globalCategory')?></div>
            <div class="table-item width-1"><?=$responseData->getLocalValue('globalStatus')?></div>
          </div>
<?php
/** @var Article $article */
foreach ($responseData->getArticles() as $article) {
    $articleTitle = $article->getStatusId() === ArticleStatus::PUBLISHED
        ? '<a href="' . $responseData->getBaseUrlWithLanguage() . $article->getUri() . '">' . $article->getTitle() . '</a>'
        : $article->getTitle();
?>
              <div class="table-row">
                <div class="table-item edit flex-no-grow"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/<?=$article->getId()?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
                <div class="table-item flex-grow-2"><?=$articleTitle?></div>
                <div class="table-item flex-grow-2"><?=$article->getTagsAsString()?></div>
                <div class="table-item width-1"><?=$responseData->getLocalValue('articleType' . $article->getTypeName()); ?></div>
                <div class="table-item width-1"><?=$responseData->getLocalValue('articleStatus' . $article->getStatusName()); ?></div>
              </div>
<?php } ?>
        </div>
      </section>
    </section>
  </main>
