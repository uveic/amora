<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base-000.css" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('partials/header', ['responseData' => $responseData])?>
<main>
  <article class="narrow-width m-t-4 m-b-6">
    <h1 class="article-title"><?=$responseData->getLocalValue('globalPageNotFoundTitle')?></h1>
    <p class="m-b-6"><?=sprintf($responseData->getLocalValue('globalPageNotFoundContent'), UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage))?></p>
  </article>
</main>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
