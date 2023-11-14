<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_media` ADD COLUMN `filename_source` VARCHAR(255) NULL DEFAULT NULL AFTER `caption_html`;

    UPDATE `core_media` SET `filename_source` = `caption_html` WHERE 1;
    UPDATE `core_media` SET `caption_html` = NULL WHERE 1;
";
