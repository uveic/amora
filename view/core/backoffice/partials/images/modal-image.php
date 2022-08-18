<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="image-modal modal-wrapper null">
    <div class="modal-inner" style="background-color: transparent;">
      <a href="#" class="modal-close-button null">
        <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
      </a>
      <div class="image-modal-loading justify-center">
        <img src="/img/loading.gif" class="img-svg img-svg-50" alt="<?=$responseData->getLocalValue('globalLoading')?>">
      </div>
      <div class="image-wrapper null">
        <div class="image-main">

        </div>
        <div class="image-info">
          <div>
            <h1 class="m-t-0 m-r-2 image-title">Title</h1>
            <p class="image-caption">Caption</p>
            <p class="image-meta"></p>
            <a href="#" class="image-delete">Delete</a>
          </div>
          <div class="next">
            <a href="#">Previous</a>
            <a href="#">Next</a>
          </div>
        </div>
      </div>
    </div>
  </div>
