<?php
error_reporting(E_ALL);

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('date.timezone', 'Europe/Paris');
ini_set('register_globals', 0);

if (function_exists('opcache_get_configuration')) {
    ini_set('opcache.memory_consumption', 64);
    ini_set('opcache.load_comments', true);
}

$loader = require '../vendor/autoload.php';
$appPath = __DIR__ . '/App1/';

$config = (new \Pimvc\Config())->setPath($appPath . 'config/')
    ->setEnv(\Pimvc\Config::ENV_DEV)
    ->load();

(new App1\App($config))
    ->setPath($appPath)
    ->setLogger()
    ->setTranslator()
    ->run();
