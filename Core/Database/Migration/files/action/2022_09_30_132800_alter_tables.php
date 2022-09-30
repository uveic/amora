<?php
/**
 * Return SQL statement as a string
 */

return "
    RENAME TABLE `action` TO `event_raw`;
    RENAME TABLE `action_processed` TO `event_processed`;
    RENAME TABLE `action_tracking` TO `event_tracking`;

    ALTER TABLE `event_raw` ADD COLUMN `processed_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `event_raw` ADD COLUMN `lock_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
    
    ALTER TABLE `event_raw` ADD INDEX `processed_at` (`processed_at`);
    ALTER TABLE `event_raw` ADD INDEX `lock_id` (`lock_id`);
";
