<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=UrlBuilderUtil::buildBackofficeDashboardUrl($responseData->getSiteLanguage())?>"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
