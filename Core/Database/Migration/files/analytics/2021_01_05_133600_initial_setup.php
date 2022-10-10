<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `action` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned DEFAULT NULL,
        `session_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `created_at` timestamp NOT NULL,
        `url` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
        `referrer` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `client_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `action_processed` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `action_id` int unsigned NOT NULL,
        `user_id` int unsigned DEFAULT NULL,
        `processed_at` timestamp NOT NULL,
        `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `ip_country_iso_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `ip_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `action_id` (`action_id`),
        KEY `user_id` (`user_id`),
        KEY `processed_at` (`processed_at`),
        KEY `url` (`url`),
        KEY `referrer` (`referrer`),
        KEY `ip_country_iso_code` (`ip_country_iso_code`),
        KEY `ip_city` (`ip_city`),
        KEY `user_agent_platform` (`user_agent_platform`),
        KEY `user_agent_browser` (`user_agent_browser`),
        KEY `user_agent_version` (`user_agent_version`),
        CONSTRAINT `action_processed_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `action_tracking` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `action_id` int unsigned NOT NULL,
        `field` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        KEY `action_id` (`action_id`),
        CONSTRAINT `action_tracking_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
