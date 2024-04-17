<?php
/**
 * Return SQL statement as a string
 */

return "
    CREATE TABLE `event_raw_search` (
        `raw_id` int unsigned NOT NULL,
        `query` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        KEY `raw_id` (`raw_id`),
        CONSTRAINT `event_raw_search_raw_id_fk` FOREIGN KEY (`raw_id`) REFERENCES `event_raw` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
