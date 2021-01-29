<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` RENAME COLUMN `content` TO `content_html`;
    ALTER TABLE `article_history` RENAME COLUMN `content` TO `content_html`;
";
