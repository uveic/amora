<?php

return [
    'env' => 'dev',
    'siteImageUrl' => '',
    'timezone' => 'UTC',
    'phpLocale' => 'en_GB',
    'defaultSiteLanguage' => 'en',
    'baseUrl' => 'http://localhost:8888/',
    'mediaBaseDir' => '/path/to/your/code/folder/public/uploads',
    'mediaBaseUrl' => '/uploads/',
    'actionLoggerEnabled' => true,
    'registrationEnabled' => false,
    'invitationEnabled' => true,
    'db' => [
        'core' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => 'rootpass',
            'name' => 'victorgonzalez'
        ],
        'action' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => 'rootpass',
            'name' => 'victorgonzalez_action'
        ],
        'mailer' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => 'rootpass',
            'name' => 'victorgonzalez_mailer'
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
