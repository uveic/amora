<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_media` ADD COLUMN `filename_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `filename_original`;
    ALTER TABLE `core_media` RENAME COLUMN `caption` TO `caption_html`;
";
