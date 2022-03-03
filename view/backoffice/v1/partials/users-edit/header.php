<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$userToEdit = $responseData->getUserToEdit();
$closeUrl = UrlBuilderUtil::buildBackofficeUsersUrl($responseData->siteLanguageIsoCode);

?>
    <section class="page-header">
      <h3><?=($userToEdit ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalUser')?></h3>
      <div class="links">
        <a href="<?=$closeUrl?>"><img src="/img/svg/x.svg" class="img-svg img-svg-25" alt="<?=$responseData->getLocalValue('globalClose')?>"></a>
      </div>
    </section>
