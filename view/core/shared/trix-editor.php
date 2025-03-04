<?php

use Amora\Core\Value\CoreIcons;

$inputIdentifier = $trixEditorInputIdentifier ?? 'trixEditorContentHtml';

?>
    <trix-toolbar id="trixEditorToolbar">
      <div class="trix-button-row">
        <div class="trix-button-group">
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="bold" data-trix-key="b" title="Bold"><?=CoreIcons::TEXT_B?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="italic" data-trix-key="i" title="Italic"><?=CoreIcons::TEXT_ITALIC?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="strike" title="Strikethrough"><?=CoreIcons::TEXT_STRIKETHROUGH?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="href" data-trix-action="link" data-trix-key="k" title="Link"><?=CoreIcons::LINK?></button>
        </div>
        <div class="trix-button-group">
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="heading1" title="Heading"><?=CoreIcons::TEXT_H?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="quote" title="Quote"><?=CoreIcons::QUOTES?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="code" title="Code"><?=CoreIcons::CODE?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="bullet" title="Bullets"><?=CoreIcons::LIST_BULLETS?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-attribute="number" title="Numbers"><?=CoreIcons::LIST_NUMBERS?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="decreaseNestingLevel" title="Decrease Level" disabled><?=CoreIcons::TEXT_INDENT?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="increaseNestingLevel" title="Increase Level" disabled><?=CoreIcons::TEXT_OUTDENT?></button>
        </div>
        <span class="trix-button-group-spacer"></span>
        <div class="trix-button-group" data-trix-button-group="history-tools">
          <button type="button" class="trix-button trix-button--icon" data-trix-action="undo" data-trix-key="z" title="Undo" disabled><?=CoreIcons::ARROW_ARC_LEFT?></button>
          <button type="button" class="trix-button trix-button--icon" data-trix-action="redo" data-trix-key="shift+z" title="Redo" disabled><?=CoreIcons::ARROW_ARC_RIGHT?></button>
        </div>
      </div>
      <div class="trix-dialogs" data-trix-dialogs="">
        <div class="trix-dialog trix-dialog--link" data-trix-dialog="href" data-trix-dialog-attribute="href">
          <div class="trix-dialog__link-fields">
            <input type="url" name="href" class="trix-input trix-input--dialog" placeholder="Enter a URL…" aria-label="URL" required="" data-trix-input="" disabled="disabled">
            <div class="trix-button-group">
              <input type="button" class="trix-button trix-button--dialog" value="Link" data-trix-method="setAttribute">
              <input type="button" class="trix-button trix-button--dialog" value="Unlink" data-trix-method="removeAttribute">
            </div>
          </div>
        </div>
      </div>
    </trix-toolbar>
    <trix-editor toolbar="trixEditorToolbar" input="<?=$inputIdentifier?>" class="editor-content trix-editor-content"></trix-editor>
