<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_media_extra_size` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `media_id` int(10) unsigned NOT NULL,
        `width` SMALLINT(5) UNSIGNED NOT NULL,
        `filename` VARCHAR(100) NOT NULL,
        CONSTRAINT `core_media_extra_size_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
