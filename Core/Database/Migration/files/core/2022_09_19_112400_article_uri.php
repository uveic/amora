<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_article` RENAME COLUMN `uri` TO `path`;
    ALTER TABLE `core_article_history` RENAME COLUMN `uri` TO `path`;

    ALTER TABLE `core_article_previous_uri` RENAME COLUMN `uri` TO `path`;

    RENAME TABLE `core_article_previous_uri` TO `core_article_path`;
";
