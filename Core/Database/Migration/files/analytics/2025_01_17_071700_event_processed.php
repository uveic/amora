<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `event_value_language_iso_code` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_language_iso_code_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_value_referrer` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_referrer_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_value_url` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_url_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_value_user_hash` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_user_hash_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_value_user_agent_platform` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_user_agent_platform_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_value_user_agent_browser` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `value` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `event_value_user_agent_browser_value_idx` (`value`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `event_processed` (
        `raw_id` int(10) unsigned NOT NULL,
        `type_id` int(10) unsigned NOT NULL,
        `user_hash_id` int(10) unsigned NOT NULL,
        `referrer_id` int(10) unsigned NULL DEFAULT NULL,
        `language_iso_code_id` int(10) unsigned NULL DEFAULT NULL,
        `platform_id` int(10) unsigned NULL DEFAULT NULL,
        `browser_id` int(10) unsigned NULL DEFAULT NULL,
        `url_id` int(10) unsigned NULL DEFAULT NULL,
        `created_at` timestamp NOT NULL,
        UNIQUE `event_processed_raw_id_idx` (`raw_id`),
        KEY `event_processed_type_id_idx` (`type_id`),
        KEY `event_processed_user_hash_id_idx` (`user_hash_id`),
        KEY `event_processed_referrer_idx` (`referrer_id`),
        KEY `event_processed_language_iso_code_idx` (`language_iso_code_id`),
        KEY `event_processed_platform_id_idx` (`platform_id`),
        KEY `event_processed_browser_id_idx` (`browser_id`),
        KEY `event_processed_url_id_idx` (`url_id`),
        KEY `event_processed_created_at_idx` (`created_at`),
        CONSTRAINT `event_processed_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `event_type` (`id`),
        CONSTRAINT `event_processed_user_hash_id_fk` FOREIGN KEY (`user_hash_id`) REFERENCES `event_value_user_hash` (`id`),
        CONSTRAINT `event_processed_referrer_id_fk` FOREIGN KEY (`referrer_id`) REFERENCES `event_value_referrer` (`id`),
        CONSTRAINT `event_processed_language_iso_code_id_fk` FOREIGN KEY (`language_iso_code_id`) REFERENCES `event_value_language_iso_code` (`id`),
        CONSTRAINT `event_processed_platform_id_fk` FOREIGN KEY (`platform_id`) REFERENCES `event_value_user_agent_platform` (`id`),
        CONSTRAINT `event_processed_browser_id_fk` FOREIGN KEY (`browser_id`) REFERENCES `event_value_user_agent_browser` (`id`),
        CONSTRAINT `event_processed_url_id_fk` FOREIGN KEY (`url_id`) REFERENCES `event_value_url` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ALTER TABLE `event_search` ADD CONSTRAINT `event_search_raw_id_fk` FOREIGN KEY (`raw_id`) REFERENCES `event_processed` (`raw_id`);

    UPDATE `event_raw` SET `lock_id` = NULL, `processed_at` = NULL WHERE 1;
";
