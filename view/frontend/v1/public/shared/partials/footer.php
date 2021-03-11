<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/dashboard"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
