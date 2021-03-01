<?php

use Amora\Core\Model\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$this->layout('base', ['responseData' => $responseData]);

$url = trim(str_replace('account', '', $responseData->getSitePath()), ' /');

$settingsClass = '';
$passwordClass = '';
$userClass = '';
$downloadClass = '';
$deleteAccountClass = '';
switch ($url) {
    case 'settings':
        $settingsClass = ' selected';
        break;
    case 'password':
        $passwordClass = ' selected';
        break;
    case 'download':
        $downloadClass = ' selected';
        break;
    case 'delete':
        $deleteAccountClass = ' selected';
        break;
    default:
        $userClass = ' selected';
        break;
}

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="content-onboarding">
      <div class="content-account-left">
        <ul>
          <li><a class="<?=$this->e($userClass)?>" href="<?=$responseData->getBaseUrlWithLanguage()?>account"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#0432ff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="96" r="64" fill="none" stroke="#0432ff" stroke-miterlimit="10" stroke-width="16"></circle><path d="M30.989,215.99064a112.03731,112.03731,0,0,1,194.02311.002" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg><?=$this->e($responseData->getLocalValue('navAccount'))?></a></li>
          <li><a class="<?=$this->e($passwordClass)?>" href="<?=$responseData->getBaseUrlWithLanguage()?>account/password"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#0432ff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M93.16866,122.8328a71.93648,71.93648,0,1,1,40.0009,40.001l.00062-.00149L120.00244,176h-24v24h-24v24h-40V184l61.168-61.168Z" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="76" r="4" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16" opacity="0.5"></circle><circle cx="180" cy="76" r="12"></circle></svg><?=$this->e($responseData->getLocalValue('navChangePassword'))?></a></li>
          <li><a class="<?=$this->e($downloadClass)?>" href="<?=$responseData->getBaseUrlWithLanguage()?>account/download"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#0432ff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="86 110 128 152 170 110" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><line x1="128" y1="39.97056" x2="128" y2="151.97056" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><path d="M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg><?=$this->e($responseData->getLocalValue('navDownloadAccountData'))?></a></li>
          <li><a class="<?=$this->e($deleteAccountClass)?>" href="<?=$responseData->getBaseUrlWithLanguage()?>account/delete"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#0432ff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="215.99609" y1="56" x2="39.99609" y2="56.00005" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><line x1="104" y1="104" x2="104" y2="168" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><line x1="152" y1="104" x2="152" y2="168" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><path d="M199.99609,56.00005V208a8,8,0,0,1-8,8h-128a8,8,0,0,1-8-8v-152" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><path d="M168,56V40a16,16,0,0,0-16-16H104A16,16,0,0,0,88,40V56" fill="none" stroke="#0432ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg><?=$this->e($responseData->getLocalValue('navDeleteAccount'))?></a></li>
        </ul>
      </div>
      <div class="content-onboarding-right">
        <div class="content-small-width">
<?php
    switch ($url) {
        case 'settings':
            $this->insert('partials/account/event', ['responseData' => $responseData]);
            break;
        case 'password':
            $this->insert('partials/account/password', ['responseData' => $responseData]);
            break;
        case 'download':
            $this->insert('partials/account/download', ['responseData' => $responseData]);
            break;
        case 'delete':
            $this->insert('partials/account/delete', ['responseData' => $responseData]);
            break;
        default:
            $this->insert('partials/account/user', ['responseData' => $responseData]);
    }
?>
        </div>
      </div>
    </section>
  </main>
