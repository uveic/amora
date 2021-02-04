<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/dashboard"><?=$responseData->getLocalValue('navAdminDashboard')?></a>
<?php } ?>
</footer>
<script type="module" src="/js/main.js"></script>
