<?php

use Amora\App\Entity\AppHtmlHomepageResponseData;

/** @var AppHtmlHomepageResponseData $responseData */

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
  <link href="/css/shared-base.css?v=000" rel="stylesheet" type="text/css">
  <script type="module" src="/js/main.js?v=000"></script>
</head>
<body>
<?=$feedbackHtml?>
<?=$this->insert('partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
