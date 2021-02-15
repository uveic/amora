<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` MODIFY COLUMN `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
    ALTER TABLE `article_history` MODIFY COLUMN `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
";
