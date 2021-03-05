<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `user` RENAME COLUMN `previous_email_address` TO `change_email_to`;
";
