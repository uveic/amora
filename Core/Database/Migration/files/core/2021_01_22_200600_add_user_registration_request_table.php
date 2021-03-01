<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `user_registration_request` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `language_id` int unsigned NULL NOT NULL,
        `created_at` timestamp NOT NULL,
        `processed_at` timestamp NULL DEFAULT NULL,
        `request_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        `user_id` int unsigned NULL DEFAULT NULL,

        PRIMARY KEY (`id`),
        UNIQUE KEY `verification_identifier` (`request_code`),
        UNIQUE KEY `email` (`email`),
        KEY `processed_at` (`processed_at`),

        CONSTRAINT `user_registration_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
        CONSTRAINT `user_registration_request_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
