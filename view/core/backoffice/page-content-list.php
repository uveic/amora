<?php

use Amora\App\Module\Form\Entity\PageContent;
use Amora\App\Util\AppUrlBuilderUtil;
use Amora\App\Value\AppPageContentType;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\Helper\ArticleHtmlGenerator;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

if (!$responseData->pageContentType) {
    return;
}

$this->layout('base', ['responseData' => $responseData]);

$pageContentBySequenceAndLanguage = [];
/** @var PageContent $pageContent */
foreach ($responseData->pageContentAll as $pageContent) {
    if (isset($pageContentBySequenceAndLanguage[$pageContent->sequence][$pageContent->language->value])) {
        continue;
    }

    $pageContentBySequenceAndLanguage[$pageContent->sequence][$pageContent->language->value] = $pageContent;
}

$newUrl = UrlBuilderUtil::buildBackofficeContentTypeSequenceNewUrl(
    language: $responseData->siteLanguage,
    contentType: $responseData->pageContentType,
    contentTypeLanguage: $responseData->siteLanguage,
);

$title = $responseData->getLocalValue('pageContentEditTitle' . $responseData->pageContentType->name) ?: $responseData->getLocalValue('pageContentEditTitle');

?>
  <main>
<?php $this->insert('partials/content-type/filter', ['responseData' => $responseData]) ?>
    <div class="page-header">
      <span class="back-js cursor-pointer"><?=CoreIcons::CARET_LEFT?></span>
      <span class="icon-one-line width-10-grow"><?=CoreIcons::ARTICLE?><span class="ellipsis"><?=$title?></span></span>
      <div class="links">
        <a href="<?=AppPageContentType::buildRedirectUrl(type: $responseData->pageContentType, language: $responseData->siteLanguage)?>"><?=CoreIcons::ARROW_SQUARE_OUT?></a>
        <a href="#" class="filter-open no-loader"><?=CoreIcons::FUNNEL?></a>
        <a href="<?=$newUrl?>"><?=CoreIcons::ADD?></a>
      </div>
    </div>
    <div class="backoffice-wrapper">
<?=ArticleHtmlGenerator::generateContentTypeFilterFilterInfoHtml($responseData)?>
      <div><?=$responseData->getLocalValue('collectionDragAndDropToOrder')?></div>
      <div class="table">
<?php
    foreach ($pageContentBySequenceAndLanguage as $pageContentItems) {
        $pageContent = $pageContentItems[$responseData->siteLanguage->value] ?? null;
        if (!$pageContent || $pageContent->isTextEmpty()) {
            $pageContent = $pageContentItems[Core::getDefaultLanguage()->value] ?? null;
        }

        if (!$pageContent || $pageContent->isTextEmpty()) {
            foreach ($pageContentItems as $pageContentItem) {
                if (
                    $pageContentItem->language === $responseData->siteLanguage ||
                    $pageContentItem->language === Core::getDefaultLanguage()
                ) {
                    continue;
                }

                if (!$pageContentItem->isTextEmpty()) {
                    $pageContent = $pageContentItem;
                    break;
                }
            }
        }

        if (!$pageContent) {
            continue;
        }

        $link = AppUrlBuilderUtil::buildBackofficeContentEditUrl(
            language: $responseData->siteLanguage,
            contentType: $pageContent->type,
            contentTypeLanguage: $pageContent->language,
            sequence: $pageContent->sequence,
        );
        $titleHtml = $link ? '<a href="' . $link . '" draggable="false">' . ($pageContent->title ?? '-') . '</a>' : '-';

        $flagsTranslatedTo = [];

        /** @var PageContent $pageContentItem */
        foreach ($pageContentItems as $pageContentItem) {
            if (!$pageContentItem->isTextEmpty()) {
                $flagsTranslatedTo[] = $pageContentItem->language->getIconFlag();
            }
        }
?>
        <div class="table-row page-content-draggable-container" draggable="true" data-page-content-id="<?=$pageContent->id?>" data-sequence="<?=$pageContent->sequence?>">
          <div class="table-item" draggable="false">
            <div draggable="false">
              <div draggable="false"><strong><?=$pageContent?->sequence?>.</strong> <?=$titleHtml?></div>
              <div class="flex-start flex-align-center m-t-05" draggable="false"><?=$responseData->getLocalValue('pageContentEditAvailableInLanguages')?>: <div class="flex-start"><?=implode('', $flagsTranslatedTo)?></div></div>
            </div>
          </div>

          <div class="table-item flex-no-grow" draggable="false">
            <span class="article-status <?=$pageContent?->status->getClass()?>" draggable="false"><?=$pageContent?->status->getIcon() . $responseData->getLocalValue('articleStatus' . $pageContent?->status->name)?></span>
          </div>
        </div>
<?php
    }
?>
      </div>
    </div>
  </main>
