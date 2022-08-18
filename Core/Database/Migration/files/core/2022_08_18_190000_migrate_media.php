<?php
/**
 * Return SQL statement as a string
 */

return "
    INSERT INTO core_media (id, user_id, type_id, status_id, path, filename_original, filename_large, filename_medium, caption, created_at, updated_at)
    SELECT
        id,
        user_id,
        2 AS type_id,
        CASE WHEN is_deleted = 1 THEN 2 ELSE 1 END AS status_id,
        NULL as path,
        REPLACE(full_url_original, '/uploads/', '') AS filename_original,
        REPLACE(full_url_large, '/uploads/', '') AS filename_large,
        REPLACE(full_url_medium, '/uploads/', '') AS filename_medium,
        caption,
        created_at,
        updated_at
    FROM image
    WHERE 1;

    ALTER TABLE `article` DROP CONSTRAINT `article_main_image_id_fk_4`;
    ALTER TABLE `article` ADD CONSTRAINT `article_main_image_id_fk` FOREIGN KEY (`main_image_id`) REFERENCES `core_media` (`id`);

    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_main_image_id_fk_5`;
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_main_image_id_fk` FOREIGN KEY (`main_image_id`) REFERENCES `core_media` (`id`);

    ALTER TABLE `article_section_image` DROP CONSTRAINT `article_section_image_ibfk_1`;
    ALTER TABLE `article_section_image` ADD CONSTRAINT `article_section_image_media_id_fk` FOREIGN KEY (`image_id`) REFERENCES `core_media` (`id`);
    ALTER TABLE `article_section_image` RENAME COLUMN `image_id` TO `media_id`;
    ALTER TABLE `article_section_image` RENAME KEY `image_id` TO `media_id`;
";
