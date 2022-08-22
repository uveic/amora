<?php
/**
 * Return SQL statement as a string
 */

return "
    RENAME TABLE `article` TO `core_article`;
    RENAME TABLE `article_history` TO `core_article_history`;
    RENAME TABLE `article_previous_uri` TO `core_article_previous_uri`;
    RENAME TABLE `article_section` TO `core_article_section`;
    RENAME TABLE `article_section_image` TO `core_article_section_image`;
    RENAME TABLE `article_section_type` TO `core_article_section_type`;
    RENAME TABLE `article_status` TO `core_article_status`;
    RENAME TABLE `article_tag_relation` TO `core_article_tag_relation`;
    RENAME TABLE `article_type` TO `core_article_type`;
    RENAME TABLE `language` TO `core_language`;
    RENAME TABLE `session` TO `core_session`;
    RENAME TABLE `tag` TO `core_tag`;
    RENAME TABLE `user` TO `core_user`;
    RENAME TABLE `user_journey_status` TO `core_user_journey_status`;
    RENAME TABLE `user_registration_request` TO `core_user_registration_request`;
    RENAME TABLE `user_role` TO `core_user_role`;
    RENAME TABLE `user_verification` TO `core_user_verification`;
    RENAME TABLE `user_verification_type` TO `core_user_verification_type`;

    DROP TABLE `image`;
";
