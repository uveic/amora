<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` ADD COLUMN `published_at` timestamp NULL DEFAULT NULL AFTER `updated_at`;
    UPDATE `article` SET `published_at` = `updated_at` WHERE 1;
    ALTER TABLE `article` ADD INDEX `published_at` (`published_at`);
";
