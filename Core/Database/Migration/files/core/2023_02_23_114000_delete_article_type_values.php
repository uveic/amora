<?php
/**
 * Return SQL statement as a string
 */

return "
    DELETE FROM core_content_history WHERE 1;
    DELETE FROM core_content WHERE 1;
    
    INSERT INTO core_content (`language_iso_code`, `user_id`, `type_id`, `created_at`, `updated_at`, `title`, `subtitle`, `html`, `main_image_id`)
    SELECT
        language_iso_code,
        user_id,
        2,
        created_at,
        updated_at,
        title,
        NULL,
        content_html,
        main_image_id
    FROM core_article
    WHERE type_id IN (100);
    
    INSERT INTO core_content (`language_iso_code`, `user_id`, `type_id`, `created_at`, `updated_at`, `title`, `subtitle`, `html`, `main_image_id`)
    SELECT
        language_iso_code,
        user_id,
        1,
        created_at,
        updated_at,
        title,
        NULL,
        content_html,
        main_image_id
    FROM core_article
    WHERE type_id IN (1);
    
    DELETE FROM core_article_history WHERE type_id IN (1, 100);
    DELETE cas FROM core_article_section AS cas
        INNER JOIN core_article AS ca ON ca.id = cas.article_id
    WHERE ca.type_id IN (1, 100);
    DELETE FROM core_article WHERE type_id IN (1, 100);
    DELETE FROM core_article_type WHERE id IN (1, 100);
";
