<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

if (!$isAdmin) {
    return;
}

?>
  <footer>
    <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navAdministrator')?></a>
  </footer>
