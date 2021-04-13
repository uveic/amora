<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();

$articleTitle = ArticleEditHtmlGenerator::generateTitleHtml($responseData);
$settings = ArticleEditHtmlGenerator::generateSettingsButtonHtml($responseData);
?>
    <section class="page-header">
        <h1><?=$articleTitle?></h1>
        <div class="links">
          <?=$settings?>
          <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles" class="m-r-1"><img src="/img/svg/x.svg" class="img-svg m-t-0" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
        </div>
    </section>
