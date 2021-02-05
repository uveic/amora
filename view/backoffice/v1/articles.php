<?php

use uve\core\module\article\model\Article;
use uve\core\model\response\HtmlResponseDataAuthorised;
use uve\core\module\article\value\ArticleStatus;
use uve\core\util\DateUtil;

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
            <div class="table-item flex-no-grow">#</div>
            <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalTitle')?></div>
            <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalUpdatedAt')?></div>
            <div class="table-item width-1"><?=$responseData->getLocalValue('globalStatus')?></div>
          </div>
<?php
/** @var Article $article */
foreach ($responseData->getArticles() as $article) {
    $articleTitle = $article->getStatusId() === ArticleStatus::PUBLISHED
        ? '<a href="' . $responseData->getBaseUrl() . $article->getUri() . '">' . $this->e($article->getTitle()) . '</a>'
        : $this->e($article->getTitle());
?>
              <div class="table-row">
                <div class="table-item edit flex-no-grow"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles/<?=$this->e($article->getId()); ?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
                <div class="table-item flex-no-grow"><?=$this->e($article->getId())?></div>
                <div class="table-item flex-grow-2"><?=$articleTitle?></div>
                <div class="table-item flex-grow-2"><?=$this->e(DateUtil::formatUtcDate($article->getUpdatedAt(), $responseData->getSiteLanguage(), false, true)); ?></div>
                <div class="table-item width-1"><?=$this->e($article->getStatusName()); ?></div>
              </div>
<?php } ?>
        </div>
      </section>
    </section>
  </main>
