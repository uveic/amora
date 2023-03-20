<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `event_processed` ADD COLUMN `user_hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `raw_id`;
    ALTER TABLE `event_processed` ADD INDEX `user_hash_idx` (`user_hash`);
";
