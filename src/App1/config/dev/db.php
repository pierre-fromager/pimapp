<?php
$localhost = '127.0.0.1';

return [
    'db0' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Mysql System',
        'name' => 'information_schema',
        'host' => $localhost,
        'user' => 'logindev',
        'port' => '3306',
        'password' => 'passworddev'
    ],
    'db1' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Pimapp Db',
        'name' => 'pimapp',
        'host' => $localhost,
        'user' => 'logindev',
        'port' => '3306',
        'password' => 'passworddev'
    ],
    'db10' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pgsql System',
        'name' => 'postgres',
        'host' => $localhost,
        'user' => 'logindev',
        'port' => '5432',
        'password' => 'passworddev'
    ],
    'db11' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pimapp Pg',
        'name' => 'pimapp',
        'host' => $localhost,
        'user' => 'logindev',
        'port' => '5432',
        'password' => 'passworddev'
    ],
    'db20' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_SQLITE,
        'label' => 'Pimapp Sqlite Sample',
        'name' => 'logger',
        'file' => '/var/www/sqlite/logger'
    ],
];
