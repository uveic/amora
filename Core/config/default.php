<?php

return [
    'env' => 'dev',
    'siteImageUrl' => '',
    'siteAdminEmail' => '',
    'siteAdminName' => '',
    'timezone' => 'UTC',
    'phpLocale' => 'en_GB',
    'defaultSiteLanguage' => 'en',
    'baseUrl' => 'http://localhost:8888/',
    'logoUrl' => '',
    'mediaBaseDir' => '/path/to/your/code/folder/public/uploads',
    'mediaBaseUrl' => '/uploads/',
    'actionLoggerEnabled' => true,
    'registrationEnabled' => false,
    'invitationEnabled' => false,
    'tagIdsForHomepage' => [-1],
    'databaseBackup' => [
        'mysqlCommandPath' => 'mysql',
        'mysqlDumpCommandPath' => 'mysqldump',
        'gzipCommandPath' => 'gzip',
        'folderPath' => '/tmp/',
    ],
    'database' => [
        'core' => [
            'host' => 'localhost',
            'user' => 'user',
            'password' => 'pass',
            'name' => 'amora_core'
        ],
        'action' => [
            'host' => 'localhost',
            'user' => 'user',
            'password' => 'pass',
            'name' => 'amora_action'
        ],
        'mailer' => [
            'host' => 'localhost',
            'user' => 'user',
            'password' => 'pass',
            'name' => 'amora_mailer'
        ]
    ],
    'mailer' => [
        'from' => [
            'email' => '',
            'name' => '',
        ],
        'replyTo' => [
            'email' => '',
            'name' => '',
        ],
        'sendgrid' => [
            'baseApiUrl' => 'https://api.sendgrid.com/v3',
            'apiKey' => ''
        ],
    ]
];
