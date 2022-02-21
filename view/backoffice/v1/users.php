<?php

use Amora\Core\Module\User\Model\User;
use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

$this->layout('base', ['responseData' => $responseData,])

?>
  <main>
    <section class="page-header">
      <h1>Users</h1>
      <div class="links">
        <a href="<?=UrlBuilderUtil::buildBackofficeNewUserUrl($responseData->getSiteLanguage())?>" class="button is-link admin-menu-button"><?=$responseData->getLocalValue('globalNew')?></a>
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
foreach ($responseData->listOfUsers as $user) {
?>
        <div class="table-row">
          <div class="table-item edit flex-no-grow"><a href="<?=UrlBuilderUtil::buildBackofficeUserUrl($responseData->getSiteLanguage(), $user->id)?>"><img class="img-svg no-margin" width="20" height="20" src="/img/svg/pencil.svg" alt="<?=$responseData->getLocalValue('globalEdit')?>"></a></div>
          <div class="table-item flex-no-grow"><?=$this->e($user->id)?></div>
          <div class="table-item flex-no-grow"><span class="enabled-icon <?=$this->e($user->isEnabled ? 'feedback-success' : 'feedback-error'); ?>"></span></div>
          <div class="table-item flex-grow-2"><?=$this->e($user->name); ?></div>
          <div class="table-item flex-grow-4"><?=$this->e($user->email); ?></div>
          <div class="table-item"><?=$this->e($user->journeyStatus->name); ?></div>
          <div class="table-item width-1<?=$user->role === UserRole::Admin ? ' is-highlighted' : ''?>"><?=$this->e($responseData->getLocalValue('userRole' . $user->role->name)); ?></div>
        </div>
<?php
}
?>
      </div>
    </section>
  </main>
