<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_media` ADD COLUMN `filename_extra_small` varchar(255) DEFAULT NULL AFTER `filename_original`;
";
