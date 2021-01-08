<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `mailer_template` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `mailer_queue` (
        `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
        `template_id` int unsigned NOT NULL COMMENT 'Email template ID',
        `reply_to_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Reply to email address',
        `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sender name',
        `receiver_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Recipient email address',
        `receiver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Recipient name',
        `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email subject',
        `content_html` text COLLATE utf8mb4_unicode_ci COMMENT 'Email content HTML',
        `fields_json` text COLLATE utf8mb4_unicode_ci COMMENT 'Dynamic email fields (JSON)',
        `created_at` timestamp NOT NULL COMMENT 'Date/Time the queue entry was created',
        `processed_at` timestamp NULL DEFAULT NULL COMMENT 'Date/Time the queue was processed',
        `has_error` tinyint(1) DEFAULT NULL COMMENT 'Has error?',
        `lock_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lock ID',
        PRIMARY KEY (`id`),
        KEY `lock_id` (`lock_id`),
        KEY `processed_at` (`processed_at`),
        KEY `has_error` (`has_error`),
        KEY `created_at` (`created_at`),
        KEY `template_id` (`template_id`),
        CONSTRAINT `mailer_queue_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `mailer_template` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `mailer_log` (
        `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
        `mailer_queue_id` int unsigned DEFAULT NULL COMMENT 'Mailer queue id (Foreign Key)',
        `request` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Request data (JSON)',
        `response` text COLLATE utf8mb4_unicode_ci COMMENT 'Response data (JSON)',
        `sent` tinyint(1) DEFAULT NULL COMMENT 'Is email sent?',
        `error_message` text COLLATE utf8mb4_unicode_ci COMMENT 'Error message (if any)',
        `created_at` timestamp NOT NULL COMMENT 'Date/Time the log entry was created',
        PRIMARY KEY (`id`),
        KEY `mailer_queue_id` (`mailer_queue_id`),
        CONSTRAINT `mailer_log_ibfk_1` FOREIGN KEY (`mailer_queue_id`) REFERENCES `mailer_queue` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
