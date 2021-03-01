<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Model\ArticleSection;
use Amora\Core\Module\Article\Value\ArticleSectionType;

/** @var HtmlResponseDataAuthorised $responseData */
$article = $responseData->getFirstArticle();
$articleSections = $responseData->getArticleSections();
$images = [];

$this->layout('base', ['responseData' => $responseData]);

function getClassName(int $sectionTypeId): string
{
    return match ($sectionTypeId) {
        ArticleSectionType::TEXT_PARAGRAPH => 'pexego-section-paragraph',
        ArticleSectionType::TEXT_TITLE => 'pexego-section-title',
        ArticleSectionType::TEXT_SUBTITLE => 'pexego-section-subtitle',
        ArticleSectionType::IMAGE => 'pexego-section-image',
        ArticleSectionType::YOUTUBE_VIDEO => 'pexego-section-video'
    };
}

function getControlButtonsHtml(
    HtmlResponseDataAuthorised $responseData,
    int $sectionId,
    bool $isFirst,
    bool $isLast
): string
{
    return PHP_EOL . '<div class="pexego-section-controls">' . PHP_EOL
        . '<a href="#" id="pexego-section-button-up-' . $sectionId . '" class="pexego-section-button pexego-section-button-up' . ($isFirst ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveUp') . '" alt="' . $responseData->getLocalValue('sectionMoveUp') . '" src="/img/svg/arrow-fat-up.svg"></a>' . PHP_EOL
        . '<a href="#" id="pexego-section-button-down-' . $sectionId . '" class="pexego-section-button pexego-section-button-down' . ($isLast ? ' null' : '') . '"><img class="img-svg" title="' . $responseData->getLocalValue('sectionMoveDown') . '" alt="' . $responseData->getLocalValue('sectionMoveDown') . '" src="/img/svg/arrow-fat-down.svg"></a>' . PHP_EOL
        . '<a href="#" id="pexego-section-button-delete-' . $sectionId . '" class="pexego-section-button pexego-section-button-delete"><img class="img-svg" title="' . $responseData->getLocalValue('sectionRemove') . '" alt="' . $responseData->getLocalValue('sectionRemove') . '" src="/img/svg/trash.svg"></a>' . PHP_EOL
        . '</div>' . PHP_EOL;
}

function generateSection(
    HtmlResponseDataAuthorised $responseData,
    ArticleSection $articleSection
): string {
    if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
        $class = getClassName($articleSection->getArticleSectionTypeId());
        $id = $class . '-' . $articleSection->getId();
        return '<section id="' . $id . '" data-editor-id="' . $articleSection->getId() . '" class="pexego-section pexego-section-paragraph">' . PHP_EOL
            . '<div class="pexego-content-paragraph placeholder" data-placeholder="' . $responseData->getLocalValue('paragraphPlaceholder') . '" contenteditable="true">' . $articleSection->getContentHtml() . '</div>' . PHP_EOL
            . '</section>';
    }

    $class = 'pexego-section ' . getClassName($articleSection->getArticleSectionTypeId());
    return '<section class="' . $class . '" data-section-id="' . $articleSection->getId() . '">'  . PHP_EOL
        . $articleSection->getContentHtml() . PHP_EOL
        . '</section>';
}

?>
<?=$this->insert('partials/articles-edit/settings', ['responseData' => $responseData])?>
<section>
  <div id="feedback" class="feedback null"></div>
  <form action="#">
<?=$this->insert('partials/articles-edit/header', ['responseData' => $responseData])?>
<?=$this->insert('partials/articles-edit/control-bar', ['responseData' => $responseData])?>
    <div class="content-medium-width">
      <input name="articleId" type="hidden" value="<?=$article ? $article->getId() : ''?>">
      <article class="pexego-container">
<?php
    $count = 0;
    $total = count($articleSections);
    /** @var ArticleSection $articleSection */
    foreach ($articleSections as $articleSection) {
?>
        <div id="pexego-section-wrapper-<?=$articleSection->getId()?>" class="pexego-section-wrapper" data-section-id="<?=$articleSection->getId()?>">
          <?=generateSection($responseData, $articleSection)?>
          <?=getControlButtonsHtml($responseData, $articleSection->getId(), $count === 0, $count === $total - 1)?>
        </div>
<?php
        $count++;
    }
?>
      </article>
      <div class="pexego-container-output null">
<?php
    foreach ($articleSections as $articleSection) {
        if ($articleSection->getArticleSectionTypeId() === ArticleSectionType::TEXT_PARAGRAPH) {
            $editorId = getClassName($articleSection->getArticleSectionTypeId()) . '-' . $articleSection->getId();
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
