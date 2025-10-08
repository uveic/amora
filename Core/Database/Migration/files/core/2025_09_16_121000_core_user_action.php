<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `core_user_action_type` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `core_user_action` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int unsigned NOT NULL,
        `created_by_user_id` int unsigned DEFAULT NULL,
        `type_id` int unsigned NOT NULL,
        `created_at` timestamp NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `core_user_action_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`),
        CONSTRAINT `core_user_action_created_by_user_id_fk` FOREIGN KEY (`created_by_user_id`) REFERENCES `core_user` (`id`),
        CONSTRAINT `core_user_action_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `core_user_action_type` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
