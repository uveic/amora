<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_album_section_media` RENAME COLUMN `content_html` TO `caption_html`;
    ALTER TABLE `core_album_section_media` DROP COLUMN `title_html`;
";
