<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Console\Input;

class MakeMigrationCommand extends Command
{
    public string $signature = 'make:migration';
    public string $description = 'Create a new migration file';

    public function handle(Input $input): void
    {
        $name = $input->arg(0);

        if (!$name) {
            $this->error('Migration name is required', true);
            return;
        }

        $table = $input->option('table');

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $migration_dir = __DIR__ . '/../../../database/migrations/';
        
        if(!is_dir($migration_dir)) {
            mkdir($migration_dir, 0755, true);
        }

        $path = $migration_dir . $filename;

        if (file_exists($path)) {
            $this->error('Migration already exists', true);
            return;
        }

        $className = $this->makeClassName($name);

        if ($table) {
            $template = $this->getTableMigrationTemplate($className, $table);
        } else {
            $template = $this->getBlankMigrationTemplate($className);
        }

        file_put_contents($path, $template);
        $this->info("Migration database/migrations/{$filename} created.", true);
    }

    protected function makeClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    protected function getBlankMigrationTemplate(string $className): string
    {
        return <<<PHP
<?php

use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Core\LunaORM\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
PHP;
    }

    protected function getTableMigrationTemplate(string $className, string $table): string
    {
        $isCreate = str_contains(strtolower($className), 'create') && str_contains(strtolower($className), 'table');

        if ($isCreate) {
            return <<<PHP
<?php

use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Core\LunaORM\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            //
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('{$table}');
    }
};
PHP;
        }

        return <<<PHP
<?php

use Core\LunaORM\Migration;
use Core\LunaORM\Schema;
use Core\LunaORM\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{$table}', function (Blueprint \$table) {
            //
        });
    }

    public function down(): void
    {
        Schema::table('{$table}', function (Blueprint \$table) {
            //
        });
    }
};
PHP;
    }
}
