<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

if ($responseData->getUserFeedback()) {
  $class = $responseData->getUserFeedback()->isError()
  ? 'feedback-error'
  : 'feedback-success';
?>
  <div id="feedback-banner" class="<?=$class?>"><?=$responseData->getUserFeedback()->getMessage()?></div>
<?php } ?>
<section>
  <p class="m-l-1"><a href="<?=$responseData->getBaseUrlWithLanguage()?>">‹ <?=$this->e($responseData->getSiteName())?></a></p>
</section>
