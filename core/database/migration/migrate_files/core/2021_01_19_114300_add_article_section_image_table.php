<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `article_section_image` (
        `image_id` int unsigned NOT NULL,
        `article_section_id` int unsigned NOT NULL,
        `caption` VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`image_id`,`article_section_id`),
        KEY `image_id` (`image_id`),
        KEY `article_section_id` (`article_section_id`),
        CONSTRAINT `article_section_image_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`id`),
        CONSTRAINT `article_section_image_ibfk_2` FOREIGN KEY (`article_section_id`) REFERENCES `article_section` (`id`)
    ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
