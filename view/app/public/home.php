<?php

use Amora\App\Entity\AppHtmlHomepageResponseData;

/** @var AppHtmlHomepageResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
  <script type="module" src="/js/main.js?v=000"></script>
</head>
<body>
<?=$this->insert('partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
