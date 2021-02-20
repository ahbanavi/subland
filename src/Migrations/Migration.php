<?php

namespace SubLand\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder;
use Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration {
    /** @var Capsule $capsule */
    public $capsule;
    /** @var Builder $capsule */
    public $schema;

    public function init() {
        $this->capsule = new Capsule;
        $this->capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'],
            'password'  => $_ENV['DB_PASS'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();
        $this->schema->enableForeignKeyConstraints();
    }
}