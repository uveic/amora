<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_media` ADD COLUMN `filename_extra_small` varchar(255) DEFAULT NULL AFTER `filename_original`;
    ALTER TABLE `core_media` ADD COLUMN `filename_extra_large` varchar(255) DEFAULT NULL AFTER `filename_large`;
    ALTER TABLE `core_media` ADD COLUMN `width_original` smallint unsigned DEFAULT NULL AFTER `status_id`;
    ALTER TABLE `core_media` ADD COLUMN `height_original` smallint unsigned DEFAULT NULL AFTER `width_original`;

    CREATE TABLE `core_media_destroyed` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `media_id` int(10) unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        `full_path_with_name` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `media_id` (`media_id`),
        CONSTRAINT `core_media_destroyed_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
