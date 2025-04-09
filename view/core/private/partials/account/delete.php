<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$user = $responseData->request->session->user;

?>
      <h1 class="m-t-0"><?=$responseData->getLocalValue('navDeleteAccount')?></h1>
      <p><?=$responseData->getLocalValue('globalComingSoon')?></p>
