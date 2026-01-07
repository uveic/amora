<?php

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\Article\Value\PageContentStatus;
use Amora\Core\Value\CoreIcons;

/** @var HtmlResponseDataAbstract $responseData */

$statusParam = $responseData->request->getGetParam('sId');

$statusFromQuery = isset($statusParam) && PageContentStatus::tryFrom($statusParam) ? PageContentStatus::from($statusParam) : null;

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
    foreach (PageContentStatus::getAll() as $status) {
        $selected = $status === $statusFromQuery;
?>
            <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$status->value?>"><?=$responseData->getLocalValue('articleStatus' . $status->name)?></option>
<?php } ?>
          </select>
        </div>
      </div>

      <a href="#" class="button filter-button filter-content-type-submit-js">Filtrar</a>
    </div>
  </div>
