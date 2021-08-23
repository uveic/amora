<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` MODIFY COLUMN `main_image_id` int UNSIGNED DEFAULT NULL;
    ALTER TABLE `article_history` MODIFY COLUMN `main_image_id` int UNSIGNED DEFAULT NULL;

    ALTER TABLE `article` ADD CONSTRAINT `article_main_image_id_fk_4` FOREIGN KEY (`main_image_id`) REFERENCES `image` (`id`);
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_main_image_id_fk_5` FOREIGN KEY (`main_image_id`) REFERENCES `image` (`id`);

    ALTER TABLE `image` ADD COLUMN `is_deleted` BOOL NOT NULL DEFAULT 0; 
";
