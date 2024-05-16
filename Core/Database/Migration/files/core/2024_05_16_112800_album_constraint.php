<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE core_album DROP CONSTRAINT `core_album_slug_id_fk`;
    ALTER TABLE core_album ADD CONSTRAINT `core_album_slug_id_fk` FOREIGN KEY (`slug_id`) REFERENCES `core_album_slug` (`id`) ON DELETE CASCADE;

    ALTER TABLE `core_media` ADD COLUMN `uploaded_to_s3_at` timestamp NULL DEFAULT NULL;
    ALTER TABLE `core_media` ADD INDEX `core_user_uploaded_to_s3_at_idx` (`uploaded_to_s3_at`);
    ALTER TABLE `core_media` RENAME COLUMN `filename_original` TO `filename`;

    UPDATE `core_media` SET filename_source = filename WHERE filename_source IS NULL;
    UPDATE `core_media` SET filename_source = '-' WHERE filename_source IS NULL;

    ALTER TABLE `core_media` MODIFY COLUMN `filename_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL;
";
