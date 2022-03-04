<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `article` DROP CONSTRAINT `article_language_id_fk`;
    ALTER TABLE `article` DROP COLUMN `language_id`;
    ALTER TABLE `article` ADD CONSTRAINT `article_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
    ALTER TABLE `article` ADD CONSTRAINT `article_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `article_status` (`id`);
    ALTER TABLE `article` ADD CONSTRAINT `article_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `article_type` (`id`);
    ALTER TABLE `article` DROP CONSTRAINT `article_ibfk_1`;
    ALTER TABLE `article` DROP CONSTRAINT `article_ibfk_2`;
    ALTER TABLE `article` DROP CONSTRAINT `article_ibfk_3`;

    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_language_id_fk`;
    ALTER TABLE `article_history` DROP COLUMN `language_id`;

    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_article_id_fk` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `article_status` (`id`);
    ALTER TABLE `article_history` ADD CONSTRAINT `article_history_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `article_type` (`id`);
    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_ibfk_1`;
    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_ibfk_2`;
    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_ibfk_3`;
    ALTER TABLE `article_history` DROP CONSTRAINT `article_history_ibfk_4`;

    ALTER TABLE `user` DROP CONSTRAINT `user_ibfk_1`;
    ALTER TABLE `user` DROP INDEX `language_id`;
    ALTER TABLE `user` DROP COLUMN `language_id`;

    ALTER TABLE `user` DROP CONSTRAINT `user_ibfk_2`;
    ALTER TABLE `user` ADD CONSTRAINT `user_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `user_role` (`id`);
    ALTER TABLE `user` DROP CONSTRAINT `user_ibfk_3`;
    ALTER TABLE `user` ADD CONSTRAINT `user_journey_id_fk` FOREIGN KEY (`journey_id`) REFERENCES `user_journey_status` (`id`);

    ALTER TABLE `user_registration_request` DROP CONSTRAINT `user_registration_request_ibfk_2`;
    ALTER TABLE `user_registration_request` DROP INDEX `user_registration_request_ibfk_2`;
    ALTER TABLE `user_registration_request` DROP CONSTRAINT `user_registration_request_ibfk_1`;
    ALTER TABLE `user_registration_request` ADD CONSTRAINT `user_registration_request_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
    ALTER TABLE `user_registration_request` DROP COLUMN `language_id`;

    DROP TABLE `language_DELETE`;
";
