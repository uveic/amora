<?php

use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

if (!isset($menuItems)) {
    echo 'You forgot to pass the variable $menuItems to the template builder. Aborting...';
    die;
}

$siteLogoHtml = $responseData->buildSiteLogoHtml();

$userRegisteredMoreThan24HoursAgo = $responseData->minutesSinceUserRegistration() > 24 * 60;
if (!$responseData->isUserVerified() && $userRegisteredMoreThan24HoursAgo) { ?>
  <div id="feedback-banner" class="feedback-success">
    <h2 class="m-t-0"><?=$responseData->getLocalValue('authenticationVerifyEmailBannerTitle')?></h2>
    <p><?=sprintf($responseData->getLocalValue('authenticationVerifyEmailBannerContent'), $responseData->request->session->user->email, $responseData->request->session->user->id)?></p>
  </div>
<?php } ?>
<header class="max-width">
  <a class="logo" href="<?=UrlBuilderUtil::buildBaseUrl($responseData->siteLanguage)?>"><?=$siteLogoHtml?></a>
  <input type="checkbox" id="mobile-nav" class="mobile-nav">
  <nav>
    <ul>
<?php
    $i = 0;
    /** @var MenuItem $menuItem */
    foreach ($menuItems as $menuItem) {
        if (empty($menuItem->children)) {
            $class = $menuItem->class ? ' ' . $menuItem->class : '';
            echo '      <li><a href="' . $menuItem->path . '" class="nav-dropdown-item' . $class . '">' . $menuItem->text . '</a></li>';
            continue;
        }
?>
      <li>
        <div class="nav-dropdown-menu-wrapper">
          <input type="checkbox" id="nav-dropdown-toggle-<?=$i?>" class="nav-dropdown-toggle">
          <label for="nav-dropdown-toggle-<?=$i++?>" class="nav-dropdown-item nav-dropdown-toggle-label"><?=$this->e($menuItem->text) . ' ' . $menuItem->icon?></label>
          <ul class="nav-dropdown-menu">
<?php
    /** @var MenuItem $child */
    foreach ($menuItem->children as $child) {
        echo '            <li><a class="' . ($menuItem->class ?? '') . '" href="' . $child->path . '">' . $child->icon . $child->text . '</a></li>' . PHP_EOL;
    }
?>
          </ul>
        </div>
      </li>
<?php } ?>
    </ul>
  </nav>
  <label for="mobile-nav" class="mobile-nav-label"><span></span></label>
</header>
