<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/style-007.css" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('partials/header', ['responseData' => $responseData])?>
<main>
  <article>
    <h1 class="article-title"><?=$responseData->getLocalValue('globalPageNotFoundTitle')?></h1>
    <p class="m-b-6"><?=$responseData->getLocalValue('globalPageNotFoundContent')?></p>
  </article>
</main>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
