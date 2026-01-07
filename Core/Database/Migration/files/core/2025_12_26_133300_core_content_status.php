<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_content_status` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    INSERT INTO `core_content_status` (`id`, `name`) VALUES
        (1, 'Published'),
        (2, 'Draft'),
        (3, 'Deleted');

    ALTER TABLE `core_content` ADD COLUMN `status_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 NULL AFTER `type_id`;
    ALTER TABLE `core_content` ADD CONSTRAINT `core_content_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `core_content_status` (`id`);

    ALTER TABLE `core_content` ADD COLUMN `sequence` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1;
    ALTER TABLE `core_content` ADD INDEX `core_content_sequence_idx` (`sequence`);

    ALTER TABLE `core_content_history` ADD COLUMN `status_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 NULL AFTER `type_id`;
    ALTER TABLE `core_content_history` ADD CONSTRAINT `core_content_history_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `core_content_status` (`id`);

    ALTER TABLE `core_content_history` ADD COLUMN `sequence` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1;

    ALTER TABLE `core_content` ADD COLUMN `excerpt` VARCHAR(500) NULL DEFAULT NULL NULL AFTER `subtitle`;
    ALTER TABLE `core_content_history` ADD COLUMN `excerpt` VARCHAR(500) NULL DEFAULT NULL NULL AFTER `subtitle`;
";
