<?php
/**
 * Return SQL statement as a string
 */

return "
    INSERT INTO `bot_path` (`path`)
    VALUES
        ('sitemap.xml'),
        ('SetupWizard.aspx/bEZWqQBLDK'),
        ('global-protect/login.esp'),
        ('dana-na/auth/url_default/welcome.cgi'),
        ('nf/auth/getAuthenticationRequirements.do'),
        ('p/u/getAuthenticationRequirements.do'),
        ('+CSCOE+/logon.html'),
        ('autodiscover/autodiscover.json');

    UPDATE `event_processed` AS ep
        INNER JOIN `event_raw` AS er ON er.id = ep.raw_id
    SET ep.`type_id` = 3 WHERE er.url IN (
        'sitemap.xml',
        'SetupWizard.aspx/bEZWqQBLDK',
        'global-protect/login.esp',
        'dana-na/auth/url_default/welcome.cgi',
        'nf/auth/getAuthenticationRequirements.do',
        'p/u/getAuthenticationRequirements.do',
        '+CSCOE+/logon.html',
        'autodiscover/autodiscover.json'
    );
";
