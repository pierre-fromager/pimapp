<?php
$localhost = '127.0.0.1';

return [
    'db0' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Mysql System',
        'name' => 'information_schema',
        'host' => $localhost,
        'user' => 'pierre',
        'port' => '3306',
        'password' => 'pierre'
    ],
    'db1' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Pimapp Db',
        'name' => 'pimgit',
        'host' => $localhost,
        'user' => 'pierre',
        'port' => '3306',
        'password' => 'pierre'
    ],
    'db10' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pgsql System',
        'name' => 'postgres',
        'host' => $localhost,
        'user' => 'postgres',
        'port' => '5432',
        'password' => 'pierre'
    ],
    'db11' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pimapp Pg',
        'name' => 'pimapp',
        'host' => $localhost,
        'user' => 'pierre',
        'port' => '5432',
        'password' => 'pierre'
    ],
    'db20' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_SQLITE,
        'label' => 'Pimapp Sqlite Logger',
        'name' => 'logger',
        'file' => '/var/www/sqlite/logger'
    ],
];
