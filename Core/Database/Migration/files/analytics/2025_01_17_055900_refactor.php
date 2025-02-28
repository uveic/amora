<?php
/**
 * Return SQL statement as a string
 */

return "
    DROP TABLE `event_processed`;
    DROP TABLE `event_tracking`;

    ALTER TABLE `event_raw` ADD COLUMN `search_query` varchar(255) NULL DEFAULT NULL;
    UPDATE `event_raw` AS er
        INNER JOIN `event_raw_search` AS ers ON er.id = ers.raw_id
    SET er.search_query = ers.query
    WHERE er.search_query IS NULL;

    ALTER TABLE `event_raw` DROP INDEX `event_raw_url_idx`;
    DELETE FROM `event_raw_search` WHERE 1;

    ALTER TABLE `event_raw_search` DROP CONSTRAINT `event_raw_search_raw_id_fk`;
    RENAME TABLE `event_raw_search` TO `event_search`;

    DELETE FROM `event_type` WHERE id = 100;
";

