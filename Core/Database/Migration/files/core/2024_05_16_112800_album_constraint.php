<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE core_album DROP CONSTRAINT `core_album_slug_id_fk`;
    ALTER TABLE core_album ADD CONSTRAINT `core_album_slug_id_fk` FOREIGN KEY (`slug_id`) REFERENCES `core_album_slug` (`id`) ON DELETE CASCADE;
";
