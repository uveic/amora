<?php

return [
    'php_locale' => 'en_GB',
    'default_site_language' => 'en',
    'media_base_dir' => '/Users/uveic/code/victorgonzalez.eu/public/uploads',
    'media_base_url' => '/uploads',
    'site_name' => 'Víctor González',
    'site_description' => 'Crea e personaliza as túas invitacións de voda no móbil ou ordenador. Envíaas por WhatsApp ou correo electrónico. Recibe confirmacións de asistencia.',
    'site_image_url' => 'https://8deagosto.eu/img/site-image.jpg',
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
            'email' => 'oito@em7038.8deagosto.eu',
            'name' => 'DEZAOITO',
        ],
        'reply_to' => [
            'email' => 'victor.gonzalez@protonmail.com',
            'name' => 'Víctor González',
        ],
        'sendgrid' => [
            'base_api_url' => 'https://api.sendgrid.com/v3',
            'api_key' => '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'
        ]
    ]
];
