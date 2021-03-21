<?php

use Illuminate\Database\Capsule\Manager as Capsule;

error_reporting(E_ALL);
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(realpath(dirname(__FILE__) . "/../"));
$dotenv->load();

$config = require_once dirname(__DIR__) . '/config/bot_config.php';

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
