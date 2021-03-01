<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$this->insert('shared/partials/home/main', ['responseData' => $responseData])?>
<?=$this->insert('shared/partials/home/footer', ['responseData' => $responseData])?>
</body>
</html>
