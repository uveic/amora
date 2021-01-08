<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `language` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `iso_code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
        `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`),
        UNIQUE KEY `iso_code` (`iso_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `user_journey_status` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `user_role` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `user` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `language_id` int unsigned NOT NULL,
        `role_id` int unsigned NOT NULL,
        `journey_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL,
        `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `previous_email_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `password_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `bio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
        `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
        `verified` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        KEY `password_hash` (`password_hash`),
        KEY `created_at` (`created_at`),
        KEY `language_id` (`language_id`),
        KEY `role_id` (`role_id`),
        KEY `journey_id` (`journey_id`),
        CONSTRAINT `user_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`),
        CONSTRAINT `user_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `user_role` (`id`),
        CONSTRAINT `user_ibfk_3` FOREIGN KEY (`journey_id`) REFERENCES `user_journey_status` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `user_verification_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `user_verification` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `verified_at` timestamp NULL DEFAULT NULL,
        `verification_identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        `is_enabled` tinyint(1) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `verification_identifier` (`verification_identifier`),
        KEY `user_id` (`user_id`),
        KEY `type_id` (`type_id`),
        KEY `is_enabled` (`is_enabled`),
        CONSTRAINT `user_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
        CONSTRAINT `user_verification_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `user_verification_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_status` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    CREATE TABLE `article` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL,
        `status_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `main_image_src` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `uri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `status_id` (`status_id`),
        KEY `type_id` (`type_id`),
        CONSTRAINT `article_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
        CONSTRAINT `article_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `article_status` (`id`),
        CONSTRAINT `article_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `article_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_history` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `article_id` int unsigned NOT NULL,
        `user_id` int unsigned NOT NULL,
        `status_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `main_image_src` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `uri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `article_id` (`article_id`),
        KEY `title` (`title`),
        KEY `created_at` (`created_at`),
        KEY `user_id` (`user_id`),
        KEY `status_id` (`status_id`),
        KEY `type_id` (`type_id`),
        CONSTRAINT `article_history_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
        CONSTRAINT `article_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
        CONSTRAINT `article_history_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `article_status` (`id`),
        CONSTRAINT `article_history_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `article_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `image` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL,
        `full_url_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `full_url_medium` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `full_url_big` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `file_path_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `file_path_medium` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `file_path_big` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `caption` mediumtext COLLATE utf8mb4_unicode_ci,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `image_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_image_relation` (
        `image_id` int unsigned NOT NULL,
        `article_id` int unsigned NOT NULL,
        `order` int DEFAULT NULL,
        PRIMARY KEY (`image_id`,`article_id`),
        KEY `image_id` (`image_id`),
        KEY `article_id` (`article_id`),
        CONSTRAINT `article_image_relation_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`id`),
        CONSTRAINT `article_image_relation_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `session` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `sid` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
        `user_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `last_visited_at` timestamp NOT NULL,
        `valid_until` timestamp NOT NULL,
        `forced_expiration_at` timestamp NULL DEFAULT NULL,
        `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
        `ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `browser_and_platform` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `sid` (`sid`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
