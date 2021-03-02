<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$this->insert('shared/partials/home/main', ['responseData' => $responseData])?>
<?php
  if ($responseData->getSession() && $responseData->getSession()->isAdmin()) {
    echo $this->insert('shared/partials/home/tag', ['responseData' => $responseData]);
    echo $this->insert('shared/partials/home/blog', ['responseData' => $responseData]);
  }?>
<?=$this->insert('shared/partials/home/footer', ['responseData' => $responseData])?>
</body>
</html>
