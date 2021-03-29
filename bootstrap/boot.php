<?php
use Illuminate\Database\Capsule\Manager as Capsule;

error_reporting(E_ALL);
require_once 'vendor/autoload.php';
$local_lang = 'en';
$translator_arr = [];

// translator function
if (!function_exists('trans')){
    function trans($path, $params = []){
        global $local_lang, $translator_arr;
        if (!$translator_arr){
            $translator_arr = include dirname(__DIR__) . '/resources/lang/' . $local_lang . '.php';
        }
        if ($params){
            return str_replace(array_keys($params), array_values($params), $translator_arr[$path]);
        } else {
            return $translator_arr[$path];
        }
    }
}

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
