<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `mailer_queue` ADD COLUMN `user_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `id`;
    ALTER TABLE `mailer_queue` ADD INDEX `mailer_queue_user_id_idx` (`user_id`);
";
