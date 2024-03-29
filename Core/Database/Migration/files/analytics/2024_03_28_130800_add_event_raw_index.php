<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `event_processed` DROP INDEX `user_agent_version`;
    ALTER TABLE `event_processed` DROP INDEX `city`;

    UPDATE `event_raw` SET `referrer` = SUBSTRING(`referrer`, 1, 255) WHERE LENGTH(`referrer`) > 255;
    UPDATE `event_raw` SET `url` = SUBSTRING(`url`, 1, 255) WHERE LENGTH(`url`) > 255;

    ALTER TABLE `event_raw` MODIFY COLUMN `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
    ALTER TABLE `event_raw` MODIFY COLUMN `referrer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
    ALTER TABLE `event_raw` ADD INDEX `event_raw_url_idx` (`url`);
";
