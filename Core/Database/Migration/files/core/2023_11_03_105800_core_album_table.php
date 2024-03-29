<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_album_status` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE KEY
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_album_template` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE KEY
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_album_slug` (
        `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `album_id` int(10) unsigned NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        KEY `core_album_slug_album_id_idx` (`album_id`),
        KEY `core_album_slug_slug_idx` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_album` (
        `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `language_iso_code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `user_id` int(10) unsigned NOT NULL,
        `status_id` int(10) unsigned NOT NULL,
        `main_media_id` int(10) unsigned NOT NULL,
        `template_id` int(10) unsigned NOT NULL,
        `slug_id` int(10) unsigned NOT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `updated_at` TIMESTAMP NOT NULL,
        `title_html` varchar(500) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `content_html` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        KEY `core_album_language_iso_code_idx` (`language_iso_code`),
        KEY `core_album_user_id_idx` (`user_id`),
        KEY `core_album_status_id_idx` (`status_id`),
        KEY `core_album_main_media_id_idx` (`main_media_id`),
        KEY `core_album_slug_id_idx` (`slug_id`),
        CONSTRAINT `core_album_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `core_language` (`id`),
        CONSTRAINT `core_album_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`),
        CONSTRAINT `core_album_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `core_album_status` (`id`),
        CONSTRAINT `core_album_main_media_id_fk` FOREIGN KEY (`main_media_id`) REFERENCES `core_media` (`id`),
        CONSTRAINT `core_album_slug_id_fk` FOREIGN KEY (`slug_id`) REFERENCES `core_album_slug` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ALTER TABLE `core_album_slug` ADD CONSTRAINT `core_album_slug_album_id_fk` FOREIGN KEY (`album_id`) REFERENCES `core_album` (`id`);

    CREATE TABLE `core_album_section` (
        `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `album_id` int(10) unsigned NOT NULL,
        `main_media_id` int(10) unsigned NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `updated_at` TIMESTAMP NOT NULL,
        `title_html` varchar(500) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `subtitle_html` varchar(500) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `content_html` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `sequence` smallint(5) unsigned NOT NULL DEFAULT 0,
        KEY `core_album_section_album_id_idx` (`album_id`),
        KEY `core_album_section_main_media_id_idx` (`main_media_id`),
        KEY `core_album_section_sequence_idx` (`sequence`),
        CONSTRAINT `core_album_section_album_id_fk` FOREIGN KEY (`album_id`) REFERENCES `core_album` (`id`),
        CONSTRAINT `core_album_section_main_media_id_fk` FOREIGN KEY (`main_media_id`) REFERENCES `core_media` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_album_section_media` (
        `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `album_section_id` int(10) unsigned NOT NULL,
        `media_id` int(10) unsigned NOT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `updated_at` TIMESTAMP NOT NULL,
        `title_html` varchar(500) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `content_html` text COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
        `sequence` smallint(5) unsigned NOT NULL DEFAULT 0,
        KEY `core_album_section_media_album_section_id_idx` (`album_section_id`),
        KEY `core_album_section_media_media_id_idx` (`media_id`),
        KEY `core_album_section_media_sequence_idx` (`sequence`),
        CONSTRAINT `core_album_section_media_album_section_id_fk` FOREIGN KEY (`album_section_id`) REFERENCES `core_album_section` (`id`),
        CONSTRAINT `core_album_section_media_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
