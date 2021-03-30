<?php

use Amora\Core\Module\User\Model\User;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section>
      <div class="form-header m-r-1 m-l-1">
        <h1>Users</h1>
        <div class="links">
          <a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/users/new" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
        </div>
      </div>
    </section>
    <section class="content-flex-block">
      <div class="table">
        <div class="table-row header">
          <div class="table-item edit flex-no-grow"></div>
          <div class="table-item flex-no-grow">#</div>
          <div class="table-item flex-no-grow"></div>
          <div class="table-item flex-grow-2"><?=$responseData->getLocalValue('globalName')?></div>
          <div class="table-item flex-grow-4"><?=$responseData->getLocalValue('globalEmail')?></div>
          <div class="table-item"><?=$responseData->getLocalValue('globalStatus')?></div>
          <div class="table-item width-1"><?=$responseData->getLocalValue('globalRole')?></div>
        </div>
<?php
/** @var User $user */
foreach ($responseData->getListOfUsers() as $user) {
?>
        <div class="table-row">
          <div class="table-item edit flex-no-grow"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/users/<?=$this->e($user->getId()); ?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
          <div class="table-item flex-no-grow"><?=$this->e($user->getId())?></div>
          <div class="table-item flex-no-grow"><span class="enabled-icon <?=$this->e($user->isEnabled() ? 'feedback-success' : 'feedback-error'); ?>"></span></div>
          <div class="table-item flex-grow-2"><?=$this->e($user->getName()); ?></div>
          <div class="table-item flex-grow-4"><?=$this->e($user->getEmail()); ?></div>
          <div class="table-item"><?=$this->e(UserJourneyStatus::getNameForId($user->getJourneyStatusId())); ?></div>
          <div class="table-item width-1<?=$user->getRoleId() === UserRole::ADMIN ? ' is-highlighted' : ''?>"><?=$this->e($responseData->getLocalValue('userRole' . $user->getRoleName())); ?></div>
        </div>
<?php
}
?>
      </div>
    </section>
  </main>
