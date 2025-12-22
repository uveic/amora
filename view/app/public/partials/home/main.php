<?php

use Amora\App\Entity\AppHtmlHomepageResponseData;
use Amora\App\Module\Form\Entity\PageContent;
use Amora\Core\Module\Article\Value\PageContentType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var AppHtmlHomepageResponseData $responseData */

/** @var PageContent $pageContentHomepage */
$pageContentHomepage = $responseData->pageContentByTypeId[PageContentType::Homepage->value] ?? null;

$editLink = $pageContentHomepage && $responseData->request->session?->isAdmin()
    ? '<p><a class="content-edit-button" href="' .
        UrlBuilderUtil::buildBackofficeContentEditUrl(
            language: $responseData->siteLanguage,
            contentType: $pageContentHomepage->type,
            contentTypeLanguage: $pageContentHomepage->language,
        ) . '">' . strtolower($responseData->getLocalValue('globalEdit')) .
        '</a></p>'
    : '';
?>
  <article class="home-main">
<?php if ($pageContentHomepage?->title) { ?>
    <h1><?=$pageContentHomepage->title?></h1>
<?php } ?>
    <?=$pageContentHomepage?->contentHtml?>
    <?=$editLink?>
  </article>
