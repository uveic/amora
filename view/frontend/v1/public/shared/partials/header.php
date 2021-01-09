<?php

use uve\core\model\response\HtmlResponseData;
use uve\core\util\StringUtil;

/** @var HtmlResponseData $responseData */

$baseLinkUrl = StringUtil::getBaseLinkUrl($responseData->getSiteLanguage());

if ($responseData->getUserFeedback()) {
  $class = $responseData->getUserFeedback()->isError()
  ? 'feedback-error'
  : 'feedback-success';
?>
  <div id="feedback-banner" class="<?=$class?>"><?=$responseData->getUserFeedback()->getMessage()?></div>
<?php } ?>
<section>
  <p class="m-l-1"><a href="<?=$this->e($baseLinkUrl)?>">‹ <?=$this->e($responseData->getSiteName())?></a></p>
</section>
