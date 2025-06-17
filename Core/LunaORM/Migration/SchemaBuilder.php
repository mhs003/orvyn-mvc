<?php

namespace Core\LunaORM\Migration;

use PDO;

class SchemaBuilder
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $table, callable $callback): void
    {
        $columns = [];
        $tableBuilder = new TableBuilder($columns);
        $callback($tableBuilder);
        $sql = "CREATE TABLE $table (" . implode(', ', $columns) . ")";
        $this->pdo->exec($sql);
    }

    public function dropIfExists(string $table): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS $table");
    }
}
