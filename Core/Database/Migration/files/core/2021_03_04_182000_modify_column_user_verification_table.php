<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `user_verification` ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER type_id;
";
