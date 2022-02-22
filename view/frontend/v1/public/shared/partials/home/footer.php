<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$isAdmin = $responseData->request->session && $responseData->request->session->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->siteLanguageIsoCode)?>"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
