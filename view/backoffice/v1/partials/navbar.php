<?php

use Amora\Core\Menu\MenuItem;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

$userRegisteredMoreThan24HoursAgo = $responseData->minutesSinceUserRegistration() > 24 * 60;
if (!$responseData->isUserVerified() && $userRegisteredMoreThan24HoursAgo) { ?>
  <div id="feedback-banner" class="feedback-success">
      <?=sprintf($responseData->getLocalValue('authenticationVerifyEmailBanner'), $responseData->getSession()->user->email, $responseData->getSession()->user->id)?>
  </div>
<?php } ?>
<header>
  <h1 class="logo"><a class="white" href="<?=$responseData->buildBaseUrlWithLanguage()?>"><?=$this->e($responseData->getSiteName())?></a></h1>
  <input type="checkbox" id="mobile-nav" class="mobile-nav">
  <nav>
    <ul>
        <?php
        $i = 0;
        /** @var MenuItem $menuItem */
        foreach ($responseData->getMenu() as $menuItem) {
            if (empty($menuItem->getChildren())) {
                ?>
              <li><a href="<?=$menuItem->getUri()?>" class="nav-dropdown-item"><?=$menuItem->getText()?></a></li>
                <?php
                continue;
            }
            ?>
          <li>
            <div class="nav-dropdown-menu-wrapper">
              <input type="checkbox" id="nav-dropdown-toggle-<?=$i?>" class="nav-dropdown-toggle">
              <label for="nav-dropdown-toggle-<?=$i++?>" class="nav-dropdown-item nav-dropdown-toggle-label">
                  <?=$this->e($menuItem->getText()) . ' ' . $menuItem->getIcon()?>
              </label>
              <ul class="nav-dropdown-menu">
                  <?php
                  /** @var MenuItem $child */
                  foreach ($menuItem->getChildren() as $child) {
                      ?>
                    <li><a href="<?=$child->getUri()?>"><?=$child->getIcon() . $child->getText()?></a></li>
                  <?php } ?>
              </ul>
            </div>
          </li>
        <?php } ?>
    </ul>
  </nav>
  <label for="mobile-nav" class="mobile-nav-label"><span></span></label>
</header>
