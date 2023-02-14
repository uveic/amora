<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_content_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_content` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `language_iso_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL,
        `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `main_image_id` int unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `type_id` (`type_id`),
        KEY `content_language_iso_code_fk` (`language_iso_code`),
        KEY `content_main_image_id_fk` (`main_image_id`),
        CONSTRAINT `content_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `core_language` (`id`),
        CONSTRAINT `content_main_image_id_fk` FOREIGN KEY (`main_image_id`) REFERENCES `core_media` (`id`),
        CONSTRAINT `content_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `core_content_type` (`id`),
        CONSTRAINT `content_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_content_history` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `content_id` int unsigned NOT NULL,
        `language_iso_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `user_id` int unsigned NOT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `main_image_id` int unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `content_id` (`content_id`),
        KEY `user_id` (`user_id`),
        KEY `type_id` (`type_id`),
        KEY `content_history_language_iso_code_fk` (`language_iso_code`),
        KEY `content_history_main_image_id_fk` (`main_image_id`),
        CONSTRAINT `content_history_content_id_fk` FOREIGN KEY (`content_id`) REFERENCES `core_content` (`id`),
        CONSTRAINT `content_history_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `core_language` (`id`),
        CONSTRAINT `content_history_main_image_id_fk` FOREIGN KEY (`main_image_id`) REFERENCES `core_media` (`id`),
        CONSTRAINT `content_history_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `core_content_type` (`id`),
        CONSTRAINT `content_history_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
