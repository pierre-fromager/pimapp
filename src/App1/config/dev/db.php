<?php

$host = '127.0.0.1';

return [
    'db0' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Mysql System',
        'name' => 'information_schema',
        'host' => $host,
        'user' => 'logindev',
        'port' => '3306',
        'password' => 'passworddev'
    ],
    'db1' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL,
        'label' => 'Pimapp Db',
        'name' => 'pimapp',
        'host' => $host,
        'user' => 'logindev',
        'port' => '3306',
        'password' => 'passworddev'
    ],
    'db10' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pgsql System',
        'name' => 'postgres',
        'host' => $host,
        'user' => 'postgresaccountdev',
        'port' => '5432',
        'password' => 'postgrespassworddev'
    ],
    'db11' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL,
        'label' => 'Pimapp Pg',
        'name' => 'pimapp',
        'host' => $host,
        'user' => 'postgresaccountdev',
        'port' => '5432',
        'password' => 'postgrespassworddev'
    ],
    'db20' => [
        'adapter' => \Pimvc\Db\Model\Core::MODEL_ADAPTER_SQLITE,
        'label' => 'Sqlite Sample Db1',
        'name' => 'dbname1',
        'file' => '/path/to/sqlite/database1'
    ],
];
