<?php

return [
    'env' => 'dev',
    'site_name' => 'uve',
    'site_description' => '',
    'site_image_url' => '',
    'timezone' => 'UTC',
    'php_locale' => 'en',
    'default_site_language' => 'en',
    'base_url' => 'http://localhost:8888/',
    'media_base_dir' => '/path/to/your/code/folder/public/uploads',
    'media_base_url' => '/uploads/',
    'action_logger_enabled' => true,
    'registrationActionEnabled' => false,
    'db' => [
        'core' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'name' => 'uve'
        ],
        'action' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'name' => 'action'
        ],
        'mailer' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'name' => 'mailer'
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
        'gmail' => [
            'application_name' => 'Gmail API Integration',
            'credentials_path' => '~/path/to/.credentials/gmail-php-quickstart.json',
            'client_secret_path' => '/path/to/client_secret.json'
        ]
    ]
];
