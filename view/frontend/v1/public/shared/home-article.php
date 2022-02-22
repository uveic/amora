<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->siteLanguageIsoCode))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?= $this->insert('shared/partials/header', ['responseData' => $responseData]) ?>
<?=$this->insert('shared/partials/article', ['responseData' => $responseData]);?>
<?=$this->insert('shared/partials/footer', ['responseData' => $responseData])?>
</body>
</html>
