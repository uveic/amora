<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<?=$this->insert('partials/head', ['responseData' => $responseData]) ?>
<body>
<?=$this->insert('partials/header', ['responseData' => $responseData]) ?>
<main>
  <article>
    <h1 class="article-title"><?=$responseData->getLocalValue('globalPageNotFoundTitle')?></h1>
    <p class="m-b-6"><?=$responseData->getLocalValue('globalPageNotFoundContent')?></p>
    <div style="min-height: 200px"></div>
  </article>
</main>
<?=$this->insert('partials/footer', ['responseData' => $responseData])?>
</body>
</html>
