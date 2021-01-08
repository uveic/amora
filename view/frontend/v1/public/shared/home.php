<?php

use uve\core\model\response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<section class="content-medium-width m-t-6 m-b-6">
  <h1>Hi, I'm Victor 👋<br> I'm a software developer living in Lugo, Spain.</h1>
  <h3>This is my personal site. It is a work in progress. Soon(-ish) you will find here my blog and personal projects.</h3>
  <h3>Please feel free to say hi anytime.</h3>
  <h3>Have a great day!</h3>
  <div id="home-links" class="m-t-3">
    <a target="_blank" href="mailto:victor@victorgonzalez.eu">victor@victorgonzalez.eu</a>
    <a target="_blank" href="https://twitter.com/uveic">Twitter</a>
    <a target="_blank" href="https://www.linkedin.com/in/victorgonzalezcastro">Linkedin</a>
  </div>
</section>

<?=$this->insert('shared/partials/footer', ['responseData' => $responseData])?>
</body>
</html>
