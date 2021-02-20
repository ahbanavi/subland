<?php

$dotenv = Dotenv\Dotenv::createImmutable(realpath(dirname(__FILE__)));
$dotenv->load();

return
[
    'paths' => [
        'migrations' => 'database' . DIRECTORY_SEPARATOR  . 'migrations'
    ],
    'migration_base_class' => 'SubLand\Migrations\Migration',
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'name'  => $_ENV['DB_NAME'],
            'user'  => $_ENV['DB_USER'],
            'pass'  => $_ENV['DB_PASS'],
            'port' => '3306',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci'
        ]
    ],
    'version_order' => 'creation'
];
