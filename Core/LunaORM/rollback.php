<?php

require_once __DIR__ . '/../../dumper/autoload.php';
use Core\Config;
use Core\LunaORM\Connection;
use Core\LunaORM\Model;
use Core\LunaORM\Migration\SchemaBuilder;


$global_config = new Config();
$global_config->init();

Connection::connect();
Model::setConnection(Connection::getPDO());
$pdo = Connection::getPDO();

$stmt = $pdo->query("SELECT MAX(batch) FROM migrations");
$batch = $stmt->fetchColumn();

if (!$batch) {
    echo "No migrations to roll back.\n";
    exit;
}

$stmt = $pdo->prepare("SELECT migration FROM migrations WHERE batch = ?");
$stmt->execute([$batch]);
$migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach (array_reverse($migrations) as $fileName) {
    $file = __DIR__ . "/../../database/migrations/$fileName.php";
    if (!file_exists($file))
        continue;
    require_once $file;
    $class = include $file;
    if (!$class)
        continue;
    echo "Rolling back: $fileName\n";
    $schema = new SchemaBuilder($pdo);
    $class->down($schema);
    $delete = $pdo->prepare("DELETE FROM migrations WHERE migration = ?");
    $delete->execute([$fileName]);
}

echo "Rolled back batch: $batch\n";