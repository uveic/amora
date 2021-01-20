<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$isAdmin = $responseData->getSession() && $responseData->getSession()->isAdmin();

?>
<footer>
<?php if ($isAdmin) { ?>
  <a href="/backoffice/dashboard">dashboard</a>
<?php } ?>
</footer>
<script type="module" src="/js/main.js"></script>
