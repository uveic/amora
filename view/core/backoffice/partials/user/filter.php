<?php

use Amora\App\Value\AppUserRole;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAbstract $responseData */

$statusParam = $responseData->request->getGetParam('sId');
$roleParam = $responseData->request->getGetParam('rId');

$statusFromQuery = isset($statusParam) && UserStatus::tryFrom($statusParam) ? UserStatus::from($statusParam) : null;
$roleFromQuery = isset($roleParam) && (UserRole::tryFrom($roleParam) || AppUserRole::tryFrom($roleParam))
    ? (UserRole::tryfrom($roleParam) ? UserRole::from($roleParam) : AppUserRole::from($roleParam))
    : null;

?>
  <div class="filter-container null">
    <div class="filter-header">
      <span>Filtro</span>
      <div class="filter-links">
        <span class="filter-close"><?=CoreIcons::CLOSE?></span>
      </div>
    </div>
    <div class="filter-form-wrapper">
      <div class="field">
        <label for="statusId" class="label">Estado:</label>
        <div class="control">
          <select id="statusId" name="statusId">
            <option value=""></option>
<?php
    foreach (UserStatus::getAll() as $status) {
        $selected = $status === $statusFromQuery;
?>
            <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$status->value?>"><?=$status->getTitle($responseData->siteLanguage)?></option>
<?php } ?>
          </select>
        </div>
      </div>

      <div class="field">
        <label for="roleId" class="label">Rol:</label>
        <div class="control">
          <select id="roleId" name="roleId">
            <option value=""></option>
<?php
    /** @var AppUserRole $role */
    foreach (AppUserRole::getAll() as $role) {
        $selected = $role === $roleFromQuery;
?>
            <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$role->value?>"><?=$role->getTitle($responseData->siteLanguage)?></option>
<?php } ?>
          </select>
        </div>
      </div>

      <a href="#" class="button filter-button filter-user-submit-js">Filtrar</a>
    </div>
  </div>
