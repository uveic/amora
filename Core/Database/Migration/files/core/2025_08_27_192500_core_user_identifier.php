<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_user` ADD COLUMN `identifier` VARCHAR(100) NULL DEFAULT NULL AFTER `password_hash`;
    ALTER TABLE `core_user` ADD UNIQUE `core_user_identifier_uq` (`identifier`);
";
