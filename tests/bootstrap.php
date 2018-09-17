<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('date.timezone', 'Europe/Paris');
ini_set('register_globals', 0);

echo 'Booting...' . "\n";

$there = __DIR__;
$loader = require $there . '/../vendor/autoload.php';
$fwkPath = $there . '/../vendor/pier-infor/pimvc/src/';

$loader->add('Pimvc', $fwkPath);
$appPath = $there . '/../src/App1/';
$loader->add('App1', $appPath);

$loader->add('Tests', $there);

echo 'Testing...' . "\n";
