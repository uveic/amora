<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE core_album DROP CONSTRAINT `core_album_slug_id_fk`;
    ALTER TABLE core_album ADD CONSTRAINT `core_album_slug_id_fk` FOREIGN KEY (`slug_id`) REFERENCES `core_album_slug` (`id`) ON DELETE CASCADE;

    ALTER TABLE `core_media` ADD COLUMN `uploaded_to_s3_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `core_media` ADD INDEX `core_media_uploaded_to_s3_at_idx` (`uploaded_to_s3_at`);

    ALTER TABLE `core_media` ADD COLUMN `deleted_locally_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `core_media` ADD INDEX `core_media_deleted_locally_at_idx` (`deleted_locally_at`);

    ALTER TABLE `core_media` RENAME COLUMN `filename_original` TO `filename`;

    UPDATE `core_media` SET filename_source = filename WHERE filename_source IS NULL;
    UPDATE `core_media` SET filename_source = '-' WHERE filename_source IS NULL;

    ALTER TABLE `core_media` MODIFY COLUMN `filename_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL;

    CREATE TABLE `core_media_exif` (
        `media_id` int(10) unsigned NOT NULL,
        `width` int(10) NULL DEFAULT NULL,
        `height` int(10) NULL DEFAULT NULL,
        `size_bytes` int(10) NULL DEFAULT NULL,
        `camera_model` varchar(50) NULL DEFAULT NULL,
        `taken_at` timestamp NULL DEFAULT NULL,
        `exposure_time` varchar(10) NULL DEFAULT NULL,
        `iso` varchar(10) NULL DEFAULT NULL,
        PRIMARY KEY (`media_id`),
        CONSTRAINT `core_media_exif_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
