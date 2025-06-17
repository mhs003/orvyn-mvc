<?php

namespace Core\LunaORM\Migration;

use PDO;

class MigrationRunner
{
    protected PDO $pdo;
    protected string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath;
        $this->ensureMigrationsTable();
    }

    protected function ensureMigrationsTable(): void
    {
        if (config('database.usedriver') === 'sqlite') {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255),
                    batch INTEGER,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else if (config('database.usedriver') === 'mysql') {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255),
                    batch INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

        }
    }

    public function run(): void
    {
        $applied = $this->getAppliedMigrations();
        $files = glob($this->migrationsPath . '/*.php');
        $batch = $this->getNextBatch();

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $applied))
                continue;

            require_once $file;

            $class = $this->getMigrationClass($file);
            $schema = new SchemaBuilder($this->pdo);

            echo "Migrating: $name\n";
            $class->up($schema);
            $this->markAsApplied($name, $batch);
        }
    }

    protected function getMigrationClass(string $file): object
    {
        return include $file;
    }

    protected function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getNextBatch(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        return (int) $stmt->fetchColumn() + 1;
    }

    protected function markAsApplied(string $migration, int $batch): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }
}
