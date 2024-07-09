<?php
/**
 * Return SQL statement as a string
 */

return "
    DELETE FROM `core_article` WHERE `type_id` NOT IN (SELECT id FROM `core_article_type`);

    ALTER TABLE `core_article` ADD INDEX `core_article_type_id_idx` (`type_id`);
    ALTER TABLE `core_article` ADD CONSTRAINT `core_article_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `core_article_type` (`id`);
";
