<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` DROP COLUMN `main_image_src`;
    ALTER TABLE `article_history` DROP COLUMN `main_image_src`;

    ALTER TABLE `article` ADD COLUMN `main_image_id` int(11) NULL DEFAULT NULL AFTER content_html;
    ALTER TABLE `article_history` ADD COLUMN `main_image_id` int(11) NULL DEFAULT NULL AFTER content_html;

    ALTER TABLE `article` ADD CONSTRAINT `article_ibfk_4` FOREIGN KEY (`main_image_id`) REFERENCES `image` (`id`);
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_ibfk_5` FOREIGN KEY (`main_image_id`) REFERENCES `image` (`id`);
";
