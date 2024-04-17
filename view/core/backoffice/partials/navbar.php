<?php

use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

if (!isset($menuItems)) {
    echo 'You forgot to pass the variable $menuItems to the template builder. Aborting...';
    die;
}

$isAdmin = $responseData->request->session?->isAdmin() ?? false;

$siteLogoHtml = $responseData->buildSiteLogoHtml(
    siteLanguage: $responseData->siteLanguage,
    homeContent: property_exists($responseData, 'homeContent') ? $responseData->homeContent : null,
    includeSubtitle: true,
    indentation: '    ',
);

$userRegisteredMoreThan24HoursAgo = $responseData->minutesSinceUserRegistration() > 24 * 60;
if (!$responseData->isUserVerified() && $userRegisteredMoreThan24HoursAgo) { ?>
  <div id="feedback-banner" class="feedback-success">
    <h2 class="m-t-0"><?=$responseData->getLocalValue('authenticationVerifyEmailBannerTitle')?></h2>
    <p><?=sprintf($responseData->getLocalValue('authenticationVerifyEmailBannerContent'), $responseData->request->session->user->email, $responseData->request->session->user->id)?></p>
  </div>
<?php } ?>
  <div class="search-fullscreen-shadow null"></div>
  <header class="header-container">
<?=$siteLogoHtml?>
    <form action="#" method="post" id="form-search" class="form-search null">
      <div class="form-search-wrapper">
        <div class="search-result-loading null"><div class="loader-spinner"></div></div>
        <label for="search" class="null">Search</label>
<?php if (!$responseData->isPublicPage) { ?>
        <input id="searchFromPublicPage" name="searchFromPublicPage" type="hidden" value="0">
<?php } ?>
        <div class="form-search-input-wrapper">
          <img class="img-svg img-svg-30 search-action-js" width="30" height="30" src="/img/svg/magnifying-glass-bold-white.svg" alt="Search">
          <input id="search" name="search" class="header-search" type="search" placeholder="<?=$responseData->getLocalValue('globalSearch')?>..." value="">
        </div>
        <input type="submit" value="Go" class="null">
        <img class="img-svg img-svg-30 search-close-js" width="30" height="30" src="/img/svg/x.svg" alt="Close">
      </div>
      <div class="search-result-container null"></div>
    </form>
    <div class="header-right">
      <input type="checkbox" id="mobile-nav" class="mobile-nav">
      <nav class="header-navbar">
        <ul class="header-navbar-ul">
<?php
    $i = 0;
    /** @var MenuItem $menuItem */
    foreach ($menuItems as $menuItem) {
        if (empty($menuItem->children)) {
            $class = $menuItem->class ? ' ' . $menuItem->class : '';
            echo '        <li><a href="' . $menuItem->path . '" class="nav-dropdown-item' . $class . '">' . $menuItem->text . '</a></li>' . PHP_EOL;
            continue;
        }
?>
          <li>
            <div class="nav-dropdown-menu-wrapper">
              <label for="nav-dropdown-toggle-<?=$i?>" class="null">Menu</label>
              <input type="checkbox" id="nav-dropdown-toggle-<?=$i?>" class="nav-dropdown-toggle">
              <label for="nav-dropdown-toggle-<?=$i++?>" class="nav-dropdown-item nav-dropdown-toggle-label"><?=$this->e($menuItem->text) . ' ' . $menuItem->icon?></label>
              <ul class="nav-dropdown-menu">
<?php
    /** @var MenuItem $child */
    foreach ($menuItem->children as $child) {
        echo '              <li><a class="' . ($menuItem->class ?? '') . '" href="' . $child->path . '">' . $child->icon . $child->text . '</a></li>' . PHP_EOL;
    }
?>
              </ul>
            </div>
          </li>
<?php } ?>
        </ul>
      </nav>
      <label for="mobile-nav" class="mobile-nav-label"><span class="null">Menu</span><span></span></label>
      <img class="img-svg img-svg-30 search-action-js" width="30" height="30" src="/img/svg/magnifying-glass-bold-white.svg" alt="Search">
    </div>
  </header>
