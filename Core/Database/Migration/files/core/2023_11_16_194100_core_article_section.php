<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_article_section` RENAME COLUMN `order` TO `sequence`;
";
