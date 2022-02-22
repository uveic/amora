<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$user = $responseData->request->session->user;

?>
      <h1><?=$responseData->getLocalValue('navDeleteAccount')?></h1>
      <p><?=$responseData->getLocalValue('globalComingSoon')?></p>
