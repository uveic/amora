<?php
/**
 * Return SQL statement as a string
 */

return "
    RENAME TABLE `action` TO `event_raw`;
    RENAME TABLE `action_tracking` TO `event_tracking`;

    ALTER TABLE `event_raw` ADD COLUMN `processed_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `event_raw` ADD COLUMN `lock_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
    
    ALTER TABLE `event_raw` ADD INDEX `processed_at` (`processed_at`);
    ALTER TABLE `event_raw` ADD INDEX `lock_id` (`lock_id`);

    DROP TABLE `action_processed`;

    CREATE TABLE `event_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_processed` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `raw_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `referrer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `language_iso_code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `country_iso_code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_browser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_version` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `raw_id` (`raw_id`),
        KEY `type_id` (`type_id`),
        KEY `referrer` (`referrer`),
        KEY `language_iso_code` (`language_iso_code`),
        KEY `country_iso_code` (`country_iso_code`),
        KEY `city` (`city`),
        KEY `user_agent_platform` (`user_agent_platform`),
        KEY `user_agent_browser` (`user_agent_browser`),
        KEY `user_agent_version` (`user_agent_version`),
        CONSTRAINT `event_processed_raw_id_fk` FOREIGN KEY (`raw_id`) REFERENCES `event_raw` (`id`),
        CONSTRAINT `event_processed_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `event_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
