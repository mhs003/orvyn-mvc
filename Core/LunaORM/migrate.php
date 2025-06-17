<?php
require_once __DIR__ . '/../../dumper/autoload.php';

use Core\Config;
use Core\LunaORM\Connection;
use Core\LunaORM\Model;
use Core\LunaORM\Migration\MigrationRunner;


$global_config = new Config();
$global_config->init();

Connection::connect();
Model::setConnection(Connection::getPDO());

$runner = new MigrationRunner(Connection::getPDO(), __DIR__ . '/../../database/migrations');
$runner->run();

echo "All pending migrations applied.\n";