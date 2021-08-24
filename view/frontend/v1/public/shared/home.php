<?php

use Amora\Core\Model\Response\HtmlHomepageResponseData;

/** @var HtmlHomepageResponseData $responseData */

$userFeedbackHtml = '';
if ($responseData->getUserFeedback()) {
    $class = $responseData->getUserFeedback()->isSuccess()
        ? 'feedback-success'
        : 'feedback-error';
    $userFeedbackHtml = '<div id="feedback-banner" class="' . $class . '">' . $responseData->getUserFeedback()->getMessage() . '</div>';
}

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$userFeedbackHtml?>
<?=$this->insert('shared/partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/articles', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/blog', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/footer', ['responseData' => $responseData])?>
</body>
</html>
