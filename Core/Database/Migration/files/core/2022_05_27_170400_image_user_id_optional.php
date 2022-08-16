<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `image` MODIFY COLUMN `user_id` int unsigned NULL DEFAULT NULL;
";
