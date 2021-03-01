<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<!DOCTYPE html>
<html lang="<?=$this->e(strtolower($responseData->getSiteLanguage()))?>">
<?= $this->insert('shared/partials/head', ['responseData' => $responseData]) ?>
<body>
<section class="content-medium-width m-t-6 m-b-6">
  <h1 class="small">Hi, I'm Víctor 👋</h1>
  <p> I'm a software developer living in Lugo, Spain. This is my personal site. It is a work in progress. Soon(-ish) you will find here my blog and personal projects.</p>
  <p>You can send me a letter via <a target="_blank" href="mailto:victor@victorgonzalez.eu">victor@victorgonzalez.eu</a>. Feel free to say hi anytime.</p>
  <p>Have a great day!</p>
</section>
<?=$this->insert('shared/partials/footer', ['responseData' => $responseData])?>
</body>
</html>
