<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_album_slug` ADD UNIQUE (`slug`);
";
