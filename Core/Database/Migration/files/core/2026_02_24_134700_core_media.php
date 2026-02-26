<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_media` MODIFY COLUMN `caption_html` TEXT DEFAULT NULL;
";
