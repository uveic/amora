<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/dashboard"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
