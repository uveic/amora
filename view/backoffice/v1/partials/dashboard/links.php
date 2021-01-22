<?php

use uve\core\model\response\HtmlResponseDataAuthorised;

/** @var HtmlResponseDataAuthorised $responseData */

?>
        <div class="content-flex-block width-45-percent">
          <h2 class="m-t-2"><?=$responseData->getLocalValue('dashboardGoTo')?></h2>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/assets/image-black.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminImages'))?>"><a href="/backoffice/images"><?=$this->e($responseData->getLocalValue('navAdminImages'))?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/assets/note-pencil.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminArticles'))?>"><a href="/backoffice/articles"><?=$responseData->getLocalValue('navAdminArticles')?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/assets/users.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminUsers'))?>"><a href="/backoffice/users"><?=$this->e($responseData->getLocalValue('navAdminUsers'))?></a></p>
        </div>
