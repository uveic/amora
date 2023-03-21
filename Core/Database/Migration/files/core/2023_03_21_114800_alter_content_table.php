<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_content` RENAME COLUMN `title` TO `title_html`;
    ALTER TABLE `core_content` RENAME COLUMN `subtitle` TO `subtitle_html`;
    ALTER TABLE `core_content` RENAME COLUMN `html` TO `content_html`;

    ALTER TABLE `core_content_history` RENAME COLUMN `title` TO `title_html`;
    ALTER TABLE `core_content_history` RENAME COLUMN `subtitle` TO `subtitle_html`;
    ALTER TABLE `core_content_history` RENAME COLUMN `html` TO `content_html`;
";
