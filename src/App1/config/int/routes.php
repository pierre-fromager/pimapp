<?php

return [
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
    '/^(stat)$/',
    '/^(stat)\/([a-zA-Z0-9_]{1,10})/',
    '/^(api\/v1\/ping)$/', // 1st group match controller with default action
    '/^(api\/v1\/ping)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(api\/v1\/ping)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(api\/v1\/ping)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(api\/v1\/auth)$/', // 1st group match controller with default action
    '/^(api\/v1\/auth)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(api\/v1\/auth)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(api\/v1\/auth)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(api\/v1\/authsn)$/', // 1st group match controller with default action
    '/^(api\/v1\/authsn)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(api\/v1\/authsn)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(api\/v1\/authsn)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(api\/v1\/probes)$/', // 1st group match controller with default action
    '/^(api\/v1\/probes)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(api\/v1\/probes)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(api\/v1\/probes)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(metro\/lignes)$/', // 1st group match controller with default action
    '/^(metro\/lignes)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(metro\/lignes)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(metro\/lignes)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(metro\/stations)$/', // 1st group match controller with default action
    '/^(metro\/stations)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(metro\/stations)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(metro\/stations)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(database)$/', // 1st group match controller with default action
    '/^(database)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(database)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(database)\/(.*)$/', // 1st group match controller 2nd match action
    '/^(crud)$/', // 1st group match controller with default action
    '/^(crud)\/(.*?)(\?.*)/', // 3rd group match ?a=1&b=2
    '/^(crud)\/(.*?)(\/.*)/', // 3rd group match /a/1/b/2
    '/^(crud)\/(.*)$/', // 1st group match controller 2nd match action
];
