<?php

use Amora\Core\Model\Response\HtmlResponseDataAbstract;

/** @var HtmlResponseDataAbstract $responseData */

?>
  <div class="image-modal modal-wrapper null">
    <div class="modal-inner">
      <a href="#" class="modal-close-button">
        <img src="/img/svg/x-white.svg" class="img-svg img-svg-30 no-margin" alt="<?=$responseData->getLocalValue('globalClose')?>">
      </a>
      <div class="image-wrapper">
        <div class="image-main">

        </div>
        <div class="image-info">
          <div>
            <h1 class="m-t-0 m-r-2">Title</h1>
            <p>Caption</p>
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
