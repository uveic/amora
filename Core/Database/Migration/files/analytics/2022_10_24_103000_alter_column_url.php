<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `event_raw` MODIFY COLUMN `url` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
";
