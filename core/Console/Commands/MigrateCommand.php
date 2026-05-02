<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Console\Input;
use Core\LunaORM\Blueprint;
use Core\LunaORM\DB;
use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Override;
use RuntimeException;

class MigrateCommand extends Command
{
    public string $signature = 'migrate';
    public string $description = 'Run the database migrations';

    #[Override]
    public function handle(Input $input): void
    {
        $this->ensureMigrationsTable();

        $action = $input->arg(0);

        if($action === 'rollback') {
            $this->rollback();
        } else if($action === 'reset') {
            // TODO: implement ?tomorrow
        } else if($action === 'refresh') {
            // TODO: implement ?tomorrow
        } else {
            $this->migrate();
        }
    }

    protected function migrate(): void
    {
        $migrations = $this->getPendingMigrations();
        

        if(empty($migrations)) {
            $this->info("Nothing to migrate");
            return;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($migrations as $migration) {
            $this->info("Migrating: {$migration['name']}");
            $migration['instance']->up();
            $this->recordMigration($migration['name'], $batch);
            $this->info("Migrated: {$migration['name']}", true);
        }
    }

    protected function getPendingMigrations(): array
    {
        $ran = DB::select('SELECT migration FROM migrations');
        $ranNames = array_column($ran, 'migration');

        $files = glob(base_path('/database/migrations/*.php'));
        $pending = [];

        foreach($files as $file) {
            $name = basename($file, ".php");

            if(!\in_array($name, $ranNames)) {
                $instance = require_once $file;
                if($instance instanceof Migration === false) {
                    throw new RuntimeException("Invalid migration: {$name}");
                }
                $pending[] = ['name' => $name, 'instance' => $instance];
            }

        }
        return $pending;
    }

    protected function recordMigration(string $name, int $batch): void
    {
        DB::table('migrations')->insert([
            'migration' => $name,
            'batch' => $batch
        ]);
    }

    protected function getNextBatchNumber(): int
    {
        $result = DB::select('SELECT MAX(batch) as batch FROM migrations')[0];
        return (int) ($result['batch'] ?? 0) + 1;
    }

    protected function ensureMigrationsTable(): void
    {
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
        }
    }

    protected function rollback(): void
    {
        $lastBatch = DB::select('SELECT MAX(batch) as batch FROM migrations')[0]['batch'] ?? 0;

        if (!$lastBatch) {
            $this->info('Nothing to rollback.', true);
            return;
        }

        $migrations = DB::select(
            'SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC',
            [$lastBatch]
        );

        foreach ($migrations as $row) {
            $mfile = base_path("database/migrations/{$row['migration']}.php");
            if(!file_exists($mfile)) {
                throw new RuntimeException("Migration file not found: database/migrations/{$row['migration']}.php");
            }
            $migration = require_once base_path("database/migrations/{$row['migration']}.php");
            if($migration instanceof Migration === false) {
                throw new RuntimeException("Invalid migration: {$row['migration']}");
            }
            if ($migration) {
                $this->info("Rolling back: {$row['migration']}");
                $migration->down();
                DB::delete('DELETE FROM migrations WHERE migration = ?', [$row['migration']]);
                $this->info("Rolled back: {$row['migration']}", true);
            }
        }
    }
}