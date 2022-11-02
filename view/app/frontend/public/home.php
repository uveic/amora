<?php

use Amora\Core\Entity\Response\HtmlHomepageResponseData;

/** @var HtmlHomepageResponseData $responseData */

$feedbackHtml = '';
if ($responseData->feedback) {
    $class = $responseData->feedback->isSuccess
        ? 'feedback-success'
        : 'feedback-error';
    $feedbackHtml = '<div id="feedback-banner" class="' . $class . '">' . $responseData->feedback->message . '</div>';
}

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('partials/head', ['responseData' => $responseData])?>
  <link href="/css/style-003.css" rel="stylesheet" type="text/css">
  <link href="/css/app/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?=$feedbackHtml?>
<?=$this->insert('partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
  <script type="module" src="/js/main-001.js"></script>
</body>
</html>
