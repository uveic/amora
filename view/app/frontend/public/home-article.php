<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=$this->insert('partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$this->insert('partials/header', ['responseData' => $responseData]) ?>
<?=$this->insert('partials/article', ['responseData' => $responseData]);?>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
