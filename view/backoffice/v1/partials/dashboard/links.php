<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

?>
        <div class="content-flex-block width-45-percent">
          <h2 class="m-t-2"><?=$responseData->getLocalValue('dashboardGoTo')?></h2>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/image-black.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminImages'))?>"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/images"><?=$this->e($responseData->getLocalValue('navAdminImages'))?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/note-pencil.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminArticles'))?>"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/articles"><?=$responseData->getLocalValue('navAdminArticles')?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/users.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminUsers'))?>"><a href="<?=$responseData->getBaseUrlWithLanguage()?>backoffice/users"><?=$this->e($responseData->getLocalValue('navAdminUsers'))?></a></p>
        </div>
