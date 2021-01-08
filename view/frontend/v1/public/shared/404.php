<?php

use uve\core\model\response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<?= $this->insert('shared/partials/header', ['responseData' => $responseData]) ?>
<main>
  <article class="content-medium-width m-t-2 m-b-2">
    <h1 class="article-title">Page Not Found :(</h1>
    <p class="m-b-6">The page you are looking for does not exist.</p>
    <div style="min-height: 200px"></div>
  </article>
</main>
<?=$this->insert('shared/partials/footer', ['responseData' => $responseData])?>
</body>
</html>
