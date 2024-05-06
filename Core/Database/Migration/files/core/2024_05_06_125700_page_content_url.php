<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_content` ADD COLUMN `action_url` varchar(255) NULL DEFAULT NULL;
";
