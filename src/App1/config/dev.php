<?php
return [
    'app' => [
        'defaultLocale' => 'fr-FR',
        'timeZone' => 'Europe/Paris',
        'translateFilePath' => '../public/lang/',
        'langs' => [
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
        'mailer' => [
            'host' => 'smtp.mailhost.tld',
            'username' => 'postmaster@yourdomain.tld',
            'password' => 'postmaster_password',
            'secure' => 'ssl',
            'dkim' => [
                'selector' => 'selector',
                'domain' => 'example.fr',
                'passphrase' => 'testtest',
                'privateKey' => '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,B473DD1445F5FCD6

rRWa7rd11Qh3w1sKge5okp8KhZwx+a6HOdgrgt5WpMq4G4oVrcBB74cx3ZzyJyTM
vjimQKjPKgyRXya1gdISUBl0WU6TySa8Z0ujL46KBvMJSy2UHQG/VZ24zqYgShiZ
lvEO+VyuZheIOCgK+aA7SC0PaLxlA+sq9uY6cZ8o4B8V1XaomTjnYi0GAQfji4b2
bee85lDzliNTTI9zj4wA5tt/C0rYkEKyBHI59hxHggat150SOIGWXplScLnUWA0f
j6XaPaMcZ5WuPL1lO46v95hVFct6mLWySduw1rdSTvK78d4BmtcgXDrq31OuzFQ3
CsFDSGMGDimg8fHXgo4Q2a8e3UVyL1XNi4qmEdQxfbAFUUdJsDVUixuwad1B7WK8
8Co/LME9AtSOSDTQN1hopaiXxkoJZfJED0j+o4GqKmkS5QvYj/Ri+S2M3e8M26kL
d7ZhYtVpwPZ0gIJvXcVm5deF3w4dNAvL3VjBDx4R/KkFvcLY3YFSVKZPIYHeLfYx
m9p+HYAzqEKeVz4T+p2/ph3XZCHvy1PMyoBKft4BNrGYloW3GxTWAkSE80RCwaJw
2x59MGqj8m1/po5HI/oUv+bJ/0tNgqn7Xzld1IvSxY0ikU+2jklMXWouY9a7k2wK
kfiDOa4jGlqUWsiq/0MQCGD8awdE57Q5rsWhpN8/0SRwSGC1qmmPE3LRqA48B2lX
SgJ3Se0i+umhciIi4DrEa94UpofibwqTniFv+exLZ4xW6RYkvZYhiQONl4XPBsVX
sKPE2t+JRPsWICm2wk2RaaKGmdxryAg+UHTNbDVPs45sogsKPYotzA==
-----END RSA PRIVATE KEY-----'
            ]
        ]
    ],
    'request' => [
        'scheme' => 'https',
        'hostname' => '',
    ],
    'jwt' => [
        // Secret for signing the JWT's, I suggest generate it with base64_encode(openssl_random_pseudo_bytes(64))
        'secret' => '',
        'algorithm' => 'HS512',
    ],
    'middleware' => [
        'tokenizer' => App1\Middleware\Tokenizer::class,
        'jwt' => App1\Middleware\Jwt::class,
        'restfull' => App1\Middleware\Restful::class,
        'acl' => App1\Middleware\Acl::class,
    ],
    'router' => [
        'unroutable' => '!\.(ico|xml|txt|avi|htm|zip|js|ico|gif|jpg|JPG|png|css|swf|flv|m4v|mp3|mp4|ogv|webm|woff)$'
    ],
    'routes' => [
        '/!\.(ico|xml|txt|avi|htm|zip|js|ico|gif|jpg|JPG|png|css|swf|flv|m4v|mp3|mp4|ogv|webm|woff)$/',
        '/^(home)$/', // 1st group match controller with default action
        '/^(home)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(home)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(home)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(user)$/', // 1st group match controller with default action
        '/^(user)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(user)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(user)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(file)$/', // 1st group match controller with default action
        '/^(file)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(file)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(file)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(database)$/', // 1st group match controller with default action
        '/^(database)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(database)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(database)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(lang)$/', // 1st group match controller with default action
        '/^(lang)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(lang)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(lang)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(acl)$/', // 1st group match controller with default action
        '/^(acl)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(acl)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(acl)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(mail)$/', // 1st group match controller with default action
        '/^(mail)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(mail)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(mail)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(api\/v1\/ping)$/', // 1st group match controller with default action
        '/^(api\/v1\/ping)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(api\/v1\/ping)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(api\/v1\/ping)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(api\/v1\/auth)$/', // 1st group match controller with default action
        '/^(api\/v1\/auth)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(api\/v1\/auth)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(api\/v1\/auth)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(metro\/lignes)$/', // 1st group match controller with default action
        '/^(metro\/lignes)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(metro\/lignes)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(metro\/lignes)\/(.*)$/', // 1st group match controller 2nd match action
        '/^(metro\/stations)$/', // 1st group match controller with default action
        '/^(metro\/stations)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
        '/^(metro\/stations)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
        '/^(metro\/stations)\/(.*)$/', // 1st group match controller 2nd match action
    ],
    'dbPool' => [
        'db0' => [
            'adapter' => 'PdoMysql',
            'name' => 'information_schema',
            'host' => 'localhost',
            'user' => 'pi',
            'port' => '3306',
            'password' => 'po'
        ],
        'db1' => [
            'adapter' => 'PdoMysql',
            'name' => 'pimapp',
            'host' => 'localhost',
            'user' => 'pi',
            'port' => '3306',
            'password' => 'po'
        ],
        'db2' => [
            'adapter' => 'Pdopgsql',
            'name' => 'thirdpartdb',
            'host' => 'localhost',
            'user' => 'pi',
            'port' => '5432',
            'password' => 'po'
        ]
    ],
    'classes' => ['prefix' => 'App1'],
    'html' => [
        'layoutName' => 'Responsive',
        'layoutConfig' => [
            'title' => 'Pimapp Pimvc App',
            'doctype' => '<!DOCTYPE html>',
            'serverName' => '',
            'description' => 'remote probe management',
            'publisher' => '',
            'revisitafter' => '1 days',
            'robots' => 'all',
            'copyright' => '',
            'organization' => 'Pier Infor',
            'author' => 'Pierre Fromager',
            'keywords' => 'Freelance,Dev,Front,Back,Archi',
            'country' => 'France',
            'pocode' => '93320',
            'email' => 'info@pier-infor.fr',
            'street' => '34 bld anatole france',
            'city' => 'Aubervilliers',
            'twitter_link' => '',
            'github_link' => '',
            'linkedin_link' => '',
        ],
        'nav' => [
            'title' => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-heart-o',
                'link' => ''
            ],
            'items' => [
                [
                    'title' => '1st title'
                    , 'icon' => 'fa fa-cutlery'
                    , 'link' => '#'
                ],
                [
                    'title' => '2nd title'
                    , 'icon' => 'fa fa-smile'
                    , 'link' => '#'
                ]
            ]
        ]
    ]
];
