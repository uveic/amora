<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `article_previous_uri` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `article_id` int unsigned NOT NULL,
        `created_at` TIMESTAMP NOT NULL,
        `uri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uri` (`uri`),
        CONSTRAINT `uri_article_id_fk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ALTER TABLE `article` ADD COLUMN `language_id` int UNSIGNED NOT NULL DEFAULT 2 AFTER `id`;
    ALTER TABLE `article` ADD CONSTRAINT `article_language_id_fk` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`);
    ALTER TABLE `article` MODIFY COLUMN `language_id` int UNSIGNED NOT NULL;

    ALTER TABLE `article_history` ADD COLUMN `language_id` int UNSIGNED NOT NULL DEFAULT 2 AFTER `id`;
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_language_id_fk` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`);
    ALTER TABLE `article_history` MODIFY COLUMN `language_id` int UNSIGNED NOT NULL;

    ALTER TABLE `article` ADD UNIQUE (`uri`);
";
