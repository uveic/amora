<?php

use Amora\Core\Module\Article\Model\Article;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData,]);

$articleTypeIdGetParam = $responseData->request->getGetParam('atId');
$articleType = !empty($articleTypeIdGetParam) && ArticleType::tryFrom($articleTypeIdGetParam)
    ? ArticleType::from($articleTypeIdGetParam)
    : ArticleType::Page;

$pageTitle = match ($articleType) {
    ArticleType::Blog => $responseData->getLocalValue('navAdminBlogPosts'),
    ArticleType::Page => $responseData->getLocalValue('navAdminArticles'),
};

?>
  <main>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span><?=$pageTitle?></span>
      <div class="links">
        <a href="#" class="filter-open"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=UrlBuilderUtil::buildBackofficeArticleNewUrl($responseData->siteLanguage, $articleType)?>"><?=CoreIcons::ADD?></a>
      </div>
    </div>
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
