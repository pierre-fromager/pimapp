<?php
/**
 * Check chosen env in index bootstrap
 */
return [
    'app' => [
        'defaultLocale' => 'fr-FR',
        'defaultLanguage' => 'fr',
        'langs' => include 'dev/langs.php',
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
        'mailer' => include 'dev/mailer.php',
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
    'middleware' => include 'dev/middleware.php',
    'router' => [
        'unroutable' => '!\.(ico|xml|txt|avi|htm|zip|js|ico|gif|jpg|JPG|png|css|swf|flv|m4v|mp3|mp4|ogv|webm|woff)$'
    ],
    'routes' => include 'dev/routes.php',
    'dbPool' => include 'dev/db.php',
    'classes' => ['prefix' => 'App1'],
    'html' => include 'dev/html.php',
    'gis' => [
        'google' => [
            'map' => [
                'api' => [
                    'key' => 'mygooglekey'
                ]
            ]
        ]
    ]
];
