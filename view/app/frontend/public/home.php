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
<?=$this->insert('partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$feedbackHtml?>
<?=$this->insert('partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
  <script type="module" src="/js/main.js"></script>
</body>
</html>
