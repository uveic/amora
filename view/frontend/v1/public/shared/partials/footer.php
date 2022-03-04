<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAbstract $responseData */

$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguage)?>"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
