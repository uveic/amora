<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

?>
<section>
  <p class="m-l-1"><a href="<?=$responseData->buildBaseUrlWithLanguage()?>">‹ <?=$this->e($responseData->getSiteName())?></a></p>
</section>
