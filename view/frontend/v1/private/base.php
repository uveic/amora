<?php

use Amora\Core\Model\Response\HtmlResponseData;

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
  <link href="/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('partials/navbar', ['responseData' => $responseData])?>
<?=$this->section('content')?>
<?=$this->insert('partials/footer')?>
</body>
</html>
