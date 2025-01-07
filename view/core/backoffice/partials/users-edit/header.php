<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

$closeUrl = UrlBuilderUtil::buildBackofficeUserListUrl($responseData->siteLanguage);

?>
    <section class="page-header">
      <h3><?=($responseData->user ? $responseData->getLocalValue('globalEdit') : $responseData->getLocalValue('globalNew')) . ' ' . $responseData->getLocalValue('globalUser')?></h3>
      <div class="links">
        <a href="<?=$closeUrl?>"><?=CoreIcons::CLOSE?></a>
      </div>
    </section>
