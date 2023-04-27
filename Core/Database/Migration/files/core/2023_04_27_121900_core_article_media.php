<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_article_media` (
        `media_id` int(10) unsigned NOT NULL,
        `article_id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`media_id`,`article_id`),
        KEY `media_id` (`media_id`),
        KEY `article_id` (`article_id`),
        CONSTRAINT `core_article_media_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`),
        CONSTRAINT `core_article_media_article_id_fk` FOREIGN KEY (`article_id`) REFERENCES `core_article` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
