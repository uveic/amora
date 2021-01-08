<?php

use uve\core\model\response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=$this->e($responseData->getPageDescription())?>">
  <title><?=$this->e($responseData->getPageTitle())?></title>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat&display=swap" rel="stylesheet">
  <link href="/css/pell.min.css" rel="stylesheet" type="text/css">
  <link href="/css/style.css" rel="stylesheet" type="text/css">
  <link rel="preload" href="/js/xhr.js" as="script">
</head>
<body>
<?=$this->insert('partials/navbar', ['responseData' => $responseData])?>
<?=$this->section('content')?>
<?=$this->insert('partials/footer')?>
</body>
</html>
