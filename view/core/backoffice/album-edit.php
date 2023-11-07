<?php

use Amora\App\Util\AppUrlBuilderUtil;
use Amora\Core\Entity\Response\HtmlResponseDataAdmin;

/** @var HtmlResponseDataAdmin $responseData */

$this->layout('base', ['responseData' => $responseData]);

$eventDate = $responseData->eventDate;

$pageTitle = $eventDate ? ('Editar: ' . $eventDate->title) : 'Novo album';
$closeLink = $eventDate
    ? AppUrlBuilderUtil::buildBackofficeEventDateViewUrl($responseData->siteLanguage, $eventDate->id)
    : AppUrlBuilderUtil::buildBackofficeEventListUrl($responseData->siteLanguage);

$this->insert('partials/event-date/modal-select-main-image', ['responseData' => $responseData]);

?>
  <main>
    <div id="feedback" class="feedback null"></div>
    <section class="page-header">
      <h3><?=$pageTitle?></h3>
      <div class="links">
        <a href="<?=$closeLink?>"><img src="/img/svg/x.svg" class="img-svg img-svg-30" width="20" height="20" alt="Volver"></a>
      </div>
    </section>
      <div class="backoffice-wrapper">
        <form action="#" method="post" id="form-event-date-edit" class="event-date-edit-wrapper">
<?=$this->insert('partials/shared/event-edit-top', ['responseData' => $responseData]);?>
            <div class="field">
              <label for="parentEventId" class="label">Foliada:</label>
              <div class="control">
                <select id="parentEventId" name="parentEventId">
                  <option value=""></option>
<?php
    /** @var Event $event */
    foreach ($responseData->events as $event) {
        $selected = $eventDate->event && $event->id === $eventDate->event->id;
?>
                      <option<?php echo $selected ? ' selected="selected"' : ''; ?> value="<?=$event->id?>"><?=$event->title?></option>
<?php } ?>
                </select>
              </div>
              <p class="help"></p>
            </div>
            <div class="field">
              <label class="label">Data e hora:</label>
              <div class="control">
                <div class="form-two-columns form-nowrap">
                  <label for="eventDateStart" class="null">Data:</label>
                  <input class="input flex-grow-4" id="eventDateStart" name="eventDateStart" type="date" placeholder="dd/mm/aaaa" value="<?=$eventDateStartString?>" required>
                  <label for="eventTimeStart" class="label null">Hora:</label>
                  <input class="input form-time" id="eventTimeStart" name="eventTimeStart" type="time" placeholder="hh:mm" value="<?=$eventTimeStartString?>" required>
                </div>
                <p class="help"><span class="is-danger"><?=$responseData->getLocalValue('globalRequired')?></span></p>
              </div>
            </div>
<?=$this->insert('partials/shared/event-edit-bottom', ['responseData' => $responseData]);?>
        </form>
      </div>
  </main>
  <script src="/js/lib/medium-editor.min.js"></script>
