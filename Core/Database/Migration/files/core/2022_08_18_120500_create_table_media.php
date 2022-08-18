<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_media_status` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_media_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_media` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NULL DEFAULT NULL,
        `type_id` int unsigned NOT NULL,
        `status_id` int unsigned NOT NULL,
        `path` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `filename_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `filename_medium` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `filename_large` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `caption` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `updated_at` TIMESTAMP NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `type_id` (`type_id`),
        KEY `status_id` (`status_id`),
        KEY `created_at` (`created_at`),
        CONSTRAINT `core_media_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
        CONSTRAINT `core_media_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `core_media_type` (`id`),
        CONSTRAINT `core_media_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `core_media_status` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
