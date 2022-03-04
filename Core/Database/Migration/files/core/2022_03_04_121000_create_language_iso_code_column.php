<?php
/**
 * Return SQL statement as a string
 */

return "
    RENAME TABLE `language` TO `language_DELETE`;

    CREATE TABLE `language` (
        `id` VARCHAR(5) NOT NULL,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    INSERT INTO `language` (`id`, `name`) VALUES
        ('EN', 'English'),
        ('GL', 'Galego');

    ALTER TABLE `article` ADD COLUMN `language_iso_code` VARCHAR(5) NULL DEFAULT NULL AFTER `language_id`;
    ALTER TABLE `article` ADD CONSTRAINT `article_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `language` (`id`);
    UPDATE `article` SET `language_iso_code` =
        CASE
            WHEN `language_id` = 1 THEN 'EN'
            ELSE 'GL'
        END
    WHERE `language_iso_code` IS NULL;

    ALTER TABLE `article_history` ADD COLUMN `language_iso_code` VARCHAR(5) NULL DEFAULT NULL AFTER `language_id`;
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `language` (`id`);
    UPDATE `article_history` SET `language_iso_code` =
        CASE
            WHEN `language_id` = 1 THEN 'EN'
            ELSE 'GL'
        END
    WHERE `language_iso_code` IS NULL;

    ALTER TABLE `user` ADD COLUMN `language_iso_code` VARCHAR(5) NULL DEFAULT NULL AFTER `language_id`;
    ALTER TABLE `user` ADD CONSTRAINT `user_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `language` (`id`);
    UPDATE `user` SET `language_iso_code` =
        CASE
            WHEN `language_id` = 1 THEN 'EN'
            ELSE 'GL'
        END
    WHERE `language_iso_code` IS NULL;

    ALTER TABLE `user_registration_request` ADD COLUMN `language_iso_code` VARCHAR(5) NULL DEFAULT NULL AFTER `language_id`;
    ALTER TABLE `user_registration_request` ADD CONSTRAINT `user_registration_request_language_iso_code_fk` FOREIGN KEY (`language_iso_code`) REFERENCES `language` (`id`);
    UPDATE `user_registration_request` SET `language_iso_code` =
        CASE
            WHEN `language_id` = 1 THEN 'EN'
            ELSE 'GL'
        END
    WHERE `language_iso_code` IS NULL;
";
