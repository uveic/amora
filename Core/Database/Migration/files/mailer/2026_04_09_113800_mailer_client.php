<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `mailer_client` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ALTER TABLE `mailer_log` ADD COLUMN `client_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `mailer_queue_id`;
    ALTER TABLE `mailer_log` ADD CONSTRAINT `mailer_client_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `mailer_client` (`id`);

    ALTER TABLE `mailer_queue` ADD COLUMN `sender_email` VARCHAR(100) NULL DEFAULT NULL AFTER `reply_to_email`;

    ALTER TABLE `mailer_queue` MODIFY COLUMN `reply_to_email` VARCHAR(100) DEFAULT NULL COMMENT 'Reply to email address';
    ALTER TABLE `mailer_queue` MODIFY COLUMN `sender_name` varchar(100) DEFAULT NULL COMMENT 'Sender name';
    ALTER TABLE `mailer_queue` MODIFY COLUMN `receiver_email` varchar(100) NOT NULL COMMENT 'Recipient email address';
    ALTER TABLE `mailer_queue` MODIFY COLUMN `receiver_name` varchar(100) DEFAULT NULL COMMENT 'Recipient name';
";
