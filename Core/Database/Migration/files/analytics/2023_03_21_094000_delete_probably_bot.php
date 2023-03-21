<?php
/**
 * Return SQL statement as a string
 */

return "
    UPDATE `event_processed` SET type_id = 3 WHERE type_id = 4;
    DELETE FROM `event_type` WHERE id = 4;
";
