<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_content` MODIFY COLUMN `content_html` TEXT NULL DEFAULT NULL;
    ALTER TABLE `core_content_history` MODIFY COLUMN `content_html` TEXT NULL DEFAULT NULL;
";
