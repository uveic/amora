<?php
/**
 * Return SQL stagement as a string
 */

return "
    CREATE TABLE `tag` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,

        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `article_tag_relation` (
        `tag_id` int unsigned NOT NULL,
        `article_id` int unsigned NOT NULL,
        PRIMARY KEY (`tag_id`,`article_id`),
        KEY `tag_id` (`tag_id`),
        KEY `article_id` (`article_id`),
        CONSTRAINT `article_tag_relation_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`),
        CONSTRAINT `article_tag_relation_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
