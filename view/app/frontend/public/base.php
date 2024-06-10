<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
</head>
<body>
<?=$this->insert('partials/header', ['responseData' => $responseData])?>
<?=$this->section('content')?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
