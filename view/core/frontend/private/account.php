<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$this->layout('base', ['responseData' => $responseData]);

$accountPos = strpos($responseData->sitePath, 'account');
$url = trim(str_replace('account', '', substr($responseData->sitePath, $accountPos)), ' /');

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="content-onboarding">
      <div class="content-onboarding-right">
        <div class="content-narrow-width">
<?php
    switch ($url) {
        case 'password':
            $this->insert('partials/account/password', ['responseData' => $responseData]);
            break;
        default:
            $this->insert('partials/account/user', ['responseData' => $responseData]);
    }
?>
        </div>
      </div>
    </section>
  </main>
