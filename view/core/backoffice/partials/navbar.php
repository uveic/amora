<?php

use Amora\Core\Core;
use Amora\Core\Entity\Util\MenuItem;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAdmin $responseData */

if (!isset($menuItems)) {
    echo 'You forgot to pass the variable $menuItems to the template builder. Aborting...';
    die;
}

if (!isset($siteLogoHtml)) {
    echo 'You forgot to pass the variable $siteLogoHtml to the template builder. Aborting...';
    die;
}

$isAdmin = $responseData->request->session?->isAdmin() ?? false;
$isSearchEnabled = $isSearchEnabled ?? Core::getConfig()->isSearchEnabled;

if (!$responseData->feedback && $responseData->request->session?->user && !$responseData->request->session->user->isVerified()) {
$userRegisteredMoreThan24HoursAgo = round((time() - $responseData->request->session->user->createdAt->getTimestamp()) / 60);

    if ($userRegisteredMoreThan24HoursAgo > 24 * 60) {
?>
  <div id="feedback-banner" class="feedback-success">
    <h2 class="m-t-0"><?=$responseData->getLocalValue('authenticationVerifyEmailBannerTitle')?></h2>
    <p><?=sprintf($responseData->getLocalValue('authenticationVerifyEmailBannerContent'), $responseData->request->session->user->changeEmailAddressTo)?></p>
  </div>
<?php } } ?>
<?php if ($isSearchEnabled) { ?>
  <div class="search-fullscreen-shadow null"></div>
<?php } ?>
  <header id="site-top" class="header-container<?=$responseData->isPublicPage ? '' : ' header-backoffice'?>">
<?=$siteLogoHtml?>
<?php if ($isSearchEnabled) { ?>
    <form action="#" method="post" id="form-search" class="form-search null">
      <div class="form-search-wrapper">
        <div class="search-result-loading null"><div class="loader-spinner"></div></div>
        <label for="search" class="null">Search</label>
<?php if (!$responseData->isPublicPage) { ?>
        <input id="searchFromPublicPage" name="searchFromPublicPage" type="hidden" value="0">
<?php } ?>
        <div class="form-search-input-wrapper">
          <span class="search-action-js search-icon-input"><?=CoreIcons::MAGNIFYING_GLASS?></span>
          <input id="search" name="search" class="header-search" type="search" placeholder="<?=$responseData->getLocalValue('globalSearch')?>..." value="">
        </div>
        <input type="submit" value="Go" class="null">
        <span class="search-close-js"><?=CoreIcons::CLOSE?></span>
      </div>
      <div class="search-result-container null"></div>
    </form>
<?php } ?>
    <div class="header-right">
      <input type="checkbox" id="mobile-nav" class="mobile-nav">
      <label for="mobile-nav" class="mobile-nav-label"><span class="null">Mobile menu</span><span></span></label>
      <nav class="header-navbar">
        <ul class="header-navbar-ul">
<?php
    $i = 0;
    /** @var MenuItem $menuItem */
    foreach ($menuItems as $menuItem) {
        if (empty($menuItem->children)) {
            $dataset = [];
            foreach ($menuItem->dataset as $left => $right) {
                $dataset[] = $left . '="' . $right . '"';
            }
            if ($menuItem->path) {
                $class = $menuItem->class ? ' ' . $menuItem->class : '';
                echo '        <li><a href="' . $menuItem->path . '" class="nav-dropdown-item' . $class . '"' . ($dataset ? (' ' . implode(' ', $dataset)) : '') . '>' . $menuItem->text . '</a></li>' . PHP_EOL;
            } else {
                echo '        <li><span class="' . $menuItem->class . '"' . ($dataset ? (' ' . implode(' ', $dataset)) : '') . '>' . $menuItem->text . '</span></li>' . PHP_EOL;
            }
            continue;
        }
?>
          <li>
            <div class="nav-dropdown-menu-wrapper">
              <label for="nav-dropdown-toggle-<?=$i?>" class="nav-dropdown-item nav-dropdown-toggle-label"><?=$menuItem->text . ' ' . $menuItem->icon?></label>
              <input type="checkbox" id="nav-dropdown-toggle-<?=$i?>" class="nav-dropdown-toggle">
              <ul class="nav-dropdown-menu">
<?php
    /** @var MenuItem $child */
    foreach ($menuItem->children as $child) {
        $dataset = [];
        foreach ($child->dataset as $left => $right) {
            $dataset[] = $left . '="' . $right . '"';
        }
        echo '                <li><a class="' . ($child->class ?? '') . '" href="' . $child->path . '"' . ($dataset ? (' ' . implode(' ', $dataset)) : '') . '>' . $child->icon . $child->text . '</a></li>' . PHP_EOL;
    }
?>
              </ul>
            </div>
          </li>
<?php
        $i++;
    }
?>
        </ul>
      </nav>
<?php if ($isSearchEnabled) {
    echo '      <span class="search-action-js search-icon">' . CoreIcons::MAGNIFYING_GLASS . '</span>' . PHP_EOL;
} ?>
    </div>
  </header>
