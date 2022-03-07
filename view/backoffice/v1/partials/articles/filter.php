<?php
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\Article\Value\ArticleType;

/** @var HtmlResponseDataAuthorised $responseData */

$articleTypeIdGetParam = $responseData->request->getGetParam('type');
$articleType = $articleTypeIdGetParam
    ? (ArticleType::tryFrom($articleTypeIdGetParam)
        ? ArticleType::from($articleTypeIdGetParam)
        : null
    ) : null;
?>
    <section id="filter-container" class="null">
      <a href="#" id="filter-close"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      <h3>Filter</h3>
      <div>
        Article type: <?=$articleType ? $articleType->name : 'All'?>
      </div>
    </section>
