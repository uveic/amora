<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `article_section_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_section` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `article_id` int unsigned NOT NULL,
        `article_section_type_id` int unsigned NOT NULL,
        `content_html` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `order` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `updated_at` timestamp NOT NULL,

        PRIMARY KEY (`id`),
        KEY `user_id` (`article_id`),
        KEY `status_id` (`article_section_type_id`),
        CONSTRAINT `article_id_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
        CONSTRAINT `article_section_id_2` FOREIGN KEY (`article_section_type_id`) REFERENCES `article_section_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
