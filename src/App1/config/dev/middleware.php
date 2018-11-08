<?php

return [
    'tokenizer' => App1\Middleware\Tokenizer::class,
    'jwt' => App1\Middleware\Jwt::class,
    'restfull' => App1\Middleware\Restful::class,
    'acl' => App1\Middleware\Acl::class,
];
