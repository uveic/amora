<?php

use Amora\Core\Value\CoreIcons;

?>
<div class="chart-container m-b-1">
      <div class="chart-month-links chart-title-wrapper">
        <a href="#" class="left hidden">
          <?=CoreIcons::ARROW_LEFT?>
          <span></span>
        </a>
        <span class="chart-title"></span>
        <a href="#" class="right hidden">
          <span></span>
          <?=CoreIcons::ARROW_RIGHT?>
        </a>
      </div>
      <div class="chart-wrapper">
        <div class="chart-line-content">
          <canvas
              id="chart-line-canvas"
              width="400"
              height="150"
              aria-label=""
              role="img"
          >
            <p></p>
          </canvas>
        </div>
      </div>
    </div>
