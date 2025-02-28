<?php
/**
 * Return SQL statement as a string
 */

return "
    RENAME TABLE `core_album_section` TO `core_collection`;
    RENAME TABLE `core_album_section_media` TO `core_collection_media`;

    ALTER TABLE `core_collection` MODIFY COLUMN `album_id` int(10) unsigned NULL DEFAULT NULL;
    ALTER TABLE `core_collection_media` RENAME COLUMN `album_section_id` TO `collection_id`;

    ALTER TABLE `core_collection` DROP CONSTRAINT `core_album_section_album_id_fk`;
    ALTER TABLE `core_collection` DROP CONSTRAINT `core_album_section_main_media_id_fk`;
    ALTER TABLE `core_collection` DROP INDEX `core_album_section_album_id_idx`;
    ALTER TABLE `core_collection` DROP INDEX `core_album_section_main_media_id_idx`;
    ALTER TABLE `core_collection` DROP INDEX `core_album_section_sequence_idx`;

    ALTER TABLE `core_collection` ADD INDEX `core_collection_album_id_idx` (`album_id`);
    ALTER TABLE `core_collection` ADD INDEX `core_collection_main_media_id_idx` (`main_media_id`);
    ALTER TABLE `core_collection` ADD INDEX `core_collection_sequence_idx` (`sequence`);
    ALTER TABLE `core_collection` ADD CONSTRAINT `core_collection_album_id_fk` FOREIGN KEY (`album_id`) REFERENCES `core_album` (`id`);
    ALTER TABLE `core_collection` ADD CONSTRAINT `core_collection_main_media_id_fk` FOREIGN KEY (`main_media_id`) REFERENCES `core_media` (`id`);

    ALTER TABLE `core_collection_media` DROP CONSTRAINT `core_album_section_media_album_section_id_fk`;
    ALTER TABLE `core_collection_media` DROP CONSTRAINT `core_album_section_media_media_id_fk`;
    ALTER TABLE `core_collection_media` DROP INDEX `core_album_section_media_album_section_id_idx`;
    ALTER TABLE `core_collection_media` DROP INDEX `core_album_section_media_media_id_idx`;
    ALTER TABLE `core_collection_media` DROP INDEX `core_album_section_media_sequence_idx`;

    ALTER TABLE `core_collection_media` ADD INDEX `core_collection_media_collection_id_idx` (`collection_id`);
    ALTER TABLE `core_collection_media` ADD INDEX `core_collection_media_media_id_idx` (`media_id`);
    ALTER TABLE `core_collection_media` ADD INDEX `core_collection_media_sequence_idx` (`sequence`);
    ALTER TABLE `core_collection_media` ADD CONSTRAINT `core_collection_media_collection_id_fk` FOREIGN KEY (`collection_id`) REFERENCES `core_collection` (`id`);
    ALTER TABLE `core_collection_media` ADD CONSTRAINT `core_collection_media_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `core_media` (`id`);

    ALTER TABLE `core_content` ADD COLUMN `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL;
    ALTER TABLE `core_content` ADD INDEX `core_content_collection_id_idx` (`collection_id`);
    ALTER TABLE `core_content` ADD CONSTRAINT `core_content_collection_id_fk` FOREIGN KEY (`collection_id`) REFERENCES `core_collection` (`id`);
";
