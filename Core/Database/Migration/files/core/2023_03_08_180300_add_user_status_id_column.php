<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_user_status` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    INSERT INTO `core_user_status` (id, name)
    VALUES (1, 'Enabled'),
        (2, 'Disabled');

    ALTER TABLE `core_user` ADD COLUMN `status_id` int(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`;
    ALTER TABLE `core_user` ADD INDEX `core_user_status_id_idx` (`status_id`);
    ALTER TABLE `core_user` ADD CONSTRAINT `core_user_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `core_user_status` (`id`);

    UPDATE `core_user` SET status_id = 1 WHERE is_enabled = 1;
    UPDATE `core_user` SET status_id = 2 WHERE is_enabled = 0;

    ALTER TABLE `core_user` DROP COLUMN `is_enabled`;
    ALTER TABLE `core_user` DROP COLUMN `verified`;
";
