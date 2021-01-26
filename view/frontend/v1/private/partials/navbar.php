<?php
use uve\core\model\response\HtmlResponseData;
use uve\core\util\StringUtil;

/** @var HtmlResponseData $responseData */

$baseLinkUrl = StringUtil::getBaseLinkUrl($responseData->getSiteLanguage());
$userRegisteredMoreThan24HoursAgo = $responseData->minutesSinceUserRegistration() > 24 * 60;
if (!$responseData->isUserVerified() && $userRegisteredMoreThan24HoursAgo) {
?>
<div id="feedback-banner" class="feedback-success">
  Enviámosche un correo a <b><?=$this->e($responseData->getSession()->getUser()->getEmail())?></b> para verificar a túa conta.
  Se non o recibiras revisa a caixa do lixo ou <a class="verified-link" data-user-id="<?=$this->e($responseData->getSession()->getUser()->getId())?>" href="#">fai click aquí</a> e enviarémosche outro. Podes <a href="/account">modificar aquí</a> o teu enderezo se fora necesario.
</div>
<?php } ?>
<header>
  <h1 class="logo"><a class="white" href="<?=$this->e($baseLinkUrl)?>"><?=$this->e($responseData->getSiteName())?></a></h1>
  <input type="checkbox" id="nav-toggle" class="nav-toggle">
  <input type="checkbox" id="user-nav-toggle" class="user-nav-toggle">
  <nav>
    <ul>
<?php foreach ($responseData->getMenu() as $item) { ?>
      <li><a href="<?=$item['uri'] ?>" class="nav-item"><?=$item['description']?></a></li>
<?php } ?>
      <li>
        <label for="user-nav-toggle" class="nav-item user-nav-toggle-label">
          <?=$this->e($responseData->getUserName())?>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><rect width="20" height="20" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg>
        </label>
        <ul class="user-nav-menu">
          <li><a href="<?=$baseLinkUrl?>/account"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="96" r="64" fill="none" stroke="#ffffff" stroke-miterlimit="10" stroke-width="16"></circle><path d="M30.989,215.99064a112.03731,112.03731,0,0,1,194.02311.002" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg><?=$this->e($responseData->getLocalValue('navAccount'))?></a></li>
          <li><a href="<?=$baseLinkUrl?>/logout"><svg xmlns="http://www.w3.org/2000/svg" width="192" height="192" fill="#ffffff" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="174.029 86 216.029 128 174.029 170" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><line x1="104" y1="128" x2="216" y2="128" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><path d="M120,216H48a8,8,0,0,1-8-8V48a8,8,0,0,1,8-8h72" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg><?=$this->e($responseData->getLocalValue('navSignOut'))?></a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <label for="nav-toggle" class="nav-toggle-label"><span></span></label>
</header>

