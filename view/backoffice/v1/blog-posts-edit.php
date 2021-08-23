<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\Helper\ArticleEditHtmlGenerator;
use Amora\Core\Util\StringUtil;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$articleSections = $responseData->getArticleSections();
$images = [];

$this->layout('base', ['responseData' => $responseData]);

$articleTypeId = ArticleType::BLOG;

?>
<?=$this->insert('partials/articles-edit/settings', ['responseData' => $responseData])?>
<section>
  <div id="feedback" class="feedback null"></div>
  <form action="#">
<?=$this->insert('partials/articles-edit/header', ['responseData' => $responseData])?>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
    <div>
      <input name="articleId" type="hidden" value="<?=$article ? $article->getId() : ''?>">
      <input name="articleTypeId" type="hidden" value="<?=$articleTypeId?>">
      <article class="pexego-container">
        <input name="articleTitle" type="text" value="<?=$article ? $article->getTitle() : ''?>" placeholder="<?=$responseData->getLocalValue('editorTitlePlaceholder')?>" class="pexego-content-title placeholder">
<?php
    $count = 0;
    $total = count($articleSections);
    /** @var ArticleSection $articleSection */
    foreach ($articleSections as $articleSection) {
?>
        <div id="pexego-section-wrapper-<?=$articleSection->getId()?>" class="pexego-section-wrapper" data-section-id="<?=$articleSection->getId()?>">
          <?=ArticleEditHtmlGenerator::generateSection($responseData, $articleSection)?>
          <?=ArticleEditHtmlGenerator::getControlButtonsHtml(
              $responseData,
              $articleSection->getId(),
              $count === 0,
              $count === $total - 1
          );?>
        </div>
<?php
        $count++;
    }
?>
      </article>
      <div class="pexego-container-output null">
        <div id="pexego-section-paragraph-<?=StringUtil::getRandomString(5)?>"></div>
<?php
    foreach ($articleSections as $articleSection) {
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
            $editorId = ArticleEditHtmlGenerator::getClassName($articleSection->getArticleSectionTypeId()) . '-' . $articleSection->getId();
?>
        <div id="<?=$editorId?>-html">
          <?=$articleSection->getContentHtml() . PHP_EOL?>
        </div>
<?php } } ?>
      </div>
    </div>
<?=$this->insert('partials/articles-edit/add-sections', ['responseData' => $responseData])?>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
  </form>
</section>
<?php if (!$article && !$articleSections) { ?>
<script type="module">
  import {addSectionParagraph} from "../../../js/module/pexego.js";

  addSectionParagraph();
</script>
<?php } ?>
