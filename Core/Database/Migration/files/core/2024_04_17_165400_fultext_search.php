<?php
/**
 * Return SQL statement as a string
 */

return "
    ALTER TABLE `core_album` ADD FULLTEXT(`title_html`);
    ALTER TABLE `core_album_section` ADD FULLTEXT(`title_html`);
    ALTER TABLE `core_article` ADD FULLTEXT(`title`);
";
