<?php

return [
    'env' => 'dev',
    'site_image_url' => '',
    'timezone' => 'UTC',
    'php_locale' => 'en_GB',
    'default_site_language' => 'en',
    'base_url' => 'http://localhost:8888/',
    'media_base_dir' => '/path/to/your/code/folder/public/uploads',
    'media_base_url' => '/uploads/',
    'action_logger_enabled' => true,
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
        'reply_to' => [
            'email' => '',
            'name' => '',
        ],
        'sendgrid' => [
            'base_api_url' => 'https://api.sendgrid.com/v3',
            'api_key' => ''
        ],
    ]
];
