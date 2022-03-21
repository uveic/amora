<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `image` RENAME COLUMN `full_url_big` TO `full_url_large`;
    ALTER TABLE `image` RENAME COLUMN `file_path_big` TO `file_path_large`;
";
