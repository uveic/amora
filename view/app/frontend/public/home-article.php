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
<?=$this->insert('partials/article', ['responseData' => $responseData]);?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
