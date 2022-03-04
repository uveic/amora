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
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$userFeedbackHtml?>
<?=$this->insert('shared/partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/footer', ['responseData' => $responseData])?>
<script type="module" src="/js/main.js"></script>
</body>
</html>
