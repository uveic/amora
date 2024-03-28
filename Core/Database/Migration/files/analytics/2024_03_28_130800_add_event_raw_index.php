<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `event_processed` DROP INDEX `user_agent_version`;
    ALTER TABLE `event_processed` DROP INDEX `city`;

    ALTER TABLE `event_raw` ADD INDEX `event_raw_url_idx` (`url`);
";
