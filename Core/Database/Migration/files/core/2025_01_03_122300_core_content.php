<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_content` RENAME COLUMN `title_html` TO `title`;
    ALTER TABLE `core_content` RENAME COLUMN `subtitle_html` TO `subtitle`;
";
