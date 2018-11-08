<?php
/**
 * Check chosen env in index bootstrap
 */
return [
    'app' => [
        'defaultLocale' => 'fr-FR',
        'defaultLanguage' => 'fr',
        'langs' => include 'prod/langs.php',
        'timeZone' => 'Europe/Paris',
        'translateFilePath' => '../public/lang/',
        'locales' => [
            'en-US' => 'English',
            'ru-RU' => 'Russian',
            'fr-FR' => 'Français',
            'pt-BR' => 'Brasil',
            'pt-PT' => 'Portugal',
            'es-ES' => 'Espanol',
            'it-IT' => 'Italiano',
            'de-DE' => 'German',
            'nl-NL' => 'Nederlands'
        ],
        'mailer' => include 'prod/mailer.php',
    ],
    'request' => [
        'scheme' => 'https',
        'hostname' => '',
    ],
    'jwt' => [
// Secret for signing the JWT's, I suggest generate it with base64_encode(openssl_random_pseudo_bytes(64))
        'secret' => 'qACAXC/FnPbKk2JYQ1/LLFSYcJrmawZ8YAvC2g7dE+z52VWY+u+ziUPC5wp1cLhai1bo5kpFxWFMZXdtci9r6Q==',
        'algorithm' => 'HS512',
    ],
    'middleware' => include 'prod/middleware.php',
    'router' => [
        'unroutable' => '!\.(ico|xml|txt|avi|htm|zip|js|ico|gif|jpg|JPG|png|css|swf|flv|m4v|mp3|mp4|ogv|webm|woff)$'
    ],
    'routes' => include 'prod/routes.php',
    'dbPool' => include 'prod/db.php',
    'classes' => ['prefix' => 'App1'],
    'html' => include 'prod/html.php',
    'gis' => [
        'google' => [
            'map' => [
                'api' => [
                    'key' => 'AIzaSyD8hn-JMOxFb9Bm-Yvn8N5wSJ229N2P6z4'
                ]
            ]
        ]
    ]
];
