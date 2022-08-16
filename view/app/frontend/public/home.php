<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;

/** @var HtmlHomepageResponseData $responseData */

$userFeedbackHtml = '';
if ($responseData->userFeedback) {
    $class = $responseData->userFeedback->isSuccess
        ? 'feedback-success'
        : 'feedback-error';
    $userFeedbackHtml = '<div id="feedback-banner" class="' . $class . '">' . $responseData->userFeedback->message . '</div>';
}

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?= $this->insert('../../../core/frontend/public/partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$userFeedbackHtml?>
<?=$this->insert('partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('partials/home/footer', ['responseData' => $responseData])?>
<script type="module" src="/js/main.js"></script>
</body>
</html>
