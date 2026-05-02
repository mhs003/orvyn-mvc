<?php

namespace Core\LunaORM;

use Closure;

class Schema
{
    public static function driver(): string
    {
        return DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public static function create(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $blueprint->create();

        $callback($blueprint);

        static::executeBlueprint($blueprint);
    }

    public static function table(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        static::executeBlueprint($blueprint);
    }

    protected static function executeBlueprint(Blueprint $blueprint): void
    {
        $statements = $blueprint->toSql();

        foreach ($statements as $sql) {
            if (trim($sql) !== '') {
                DB::connection()->query($sql)->fetch();
            }
        }
    }

    public static function drop(string $table): void
    {
        $wrapped = static::wrap($table);
        DB::connection()->query("DROP TABLE {$wrapped}")->fetch();
    }

    public static function dropIfExists(string $table): void
    {
        $wrapped = static::wrap($table);
        $ifExists = static::driver() === 'sqlite' ? '' : 'IF EXISTS';
        DB::connection()->query("DROP TABLE {$ifExists} {$wrapped}")->fetch();
    }

    public static function hasTable(string $table): bool
    {
        $driver = static::driver();

        try {
            if ($driver === 'sqlite') {
                $result = DB::connection()->query(
                    "SELECT name FROM sqlite_master WHERE type='table' AND name=?",
                    [$table]
                )->fetch();
            } else {
                // MySQL / MariaDB
                $result = DB::connection()->query(
                    "SHOW TABLES LIKE ?",
                    [$table]
                )->fetch();
            }

            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function wrap(string $name): string
    {
        $driver = static::driver();
        return match ($driver) {
            'sqlite' => "\"{$name}\"",
            default  => "`{$name}`", // MySQL, MariaDB, etc.
        };
    }
}

class Blueprint
{
    protected string $table;
    protected bool $creating = false;
    protected array $commands = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function create(): void
    {
        $this->creating = true;
    }

    public function id(string $column = 'id'): ColumnDefinition
    {
        return $this->addColumn(
            Schema::driver() === 'sqlite' ? 'INTEGER' : 'BIGINT',
            $column,
            ['primary', 'autoincrement']
        );
    }

    public function string(string $column, int $length = 255): ColumnDefinition
    {
        return $this->addColumn("VARCHAR({$length})", $column);
    }

    public function integer(string $column): ColumnDefinition
    {
        return $this->addColumn('INT', $column);
    }

    public function bigInteger(string $column): ColumnDefinition
    {
        return $this->addColumn('BIGINT', $column);
    }

    public function text(string $column): ColumnDefinition
    {
        return $this->addColumn('TEXT', $column);
    }

    public function boolean(string $column): ColumnDefinition
    {
        $type = Schema::driver() === 'sqlite' ? 'INTEGER' : 'TINYINT(1)';
        return $this->addColumn($type, $column);
    }

    public function date(string $column): ColumnDefinition
    {
        return $this->addColumn('DATE', $column);
    }

    public function datetime(string $column): ColumnDefinition
    {
        return $this->addColumn('DATETIME', $column);
    }

    public function timestamp(string $column): ColumnDefinition
    {
        return $this->addColumn('TIMESTAMP', $column);
    }

    public function json(string $column): ColumnDefinition
    {
        $type = Schema::driver() === 'sqlite' ? 'TEXT' : 'JSON';
        return $this->addColumn($type, $column);
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
    }

    protected function addColumn(string $type, string $name, array $modifiers = []): ColumnDefinition
    {
        $column = new ColumnDefinition($name, $type, $modifiers);
        $this->commands[] = $column;
        return $column;
    }

    public function index(string|array $columns, ?string $name = null): void
    {
        $this->commands[] = new IndexDefinition($columns, 'index', $name);
    }

    public function unique(string|array $columns, ?string $name = null): void
    {
        $this->commands[] = new IndexDefinition($columns, 'unique', $name);
    }

    public function toSql(): array
    {
        $grammar = GrammarFactory::make(Schema::driver());
        return $this->creating 
            ? $grammar->compileCreate($this) 
            : $grammar->compileAlter($this);
    }

    public function getTable(): string { return $this->table; }
    public function isCreating(): bool { return $this->creating; }
    public function getCommands(): array { return $this->commands; }
}

class ColumnDefinition
{
    public function __construct(
        public readonly string $name,
        public string $type,
        public array $modifiers = []
    ) {}

    public function nullable(bool $value = true): self
    {
        $this->modifiers['nullable'] = $value;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->modifiers['default'] = $value;
        return $this;
    }

    public function unsigned(): self
    {
        $this->modifiers['unsigned'] = true;
        return $this;
    }

    public function unique(): self
    {
        $this->modifiers['unique'] = true;
        return $this;
    }

    public function useCurrentOnUpdate(): self
    {
        $this->modifiers['on_update_current_timestamp'] = true;
        return $this;
    }
}

class IndexDefinition
{
    public function __construct(
        public readonly string|array $columns,
        public readonly string $type = 'index',
        public readonly ?string $name = null
    ) {}
}

class GrammarFactory
{
    public static function make(string $driver): Grammar
    {
        return match ($driver) {
            'sqlite' => new SqliteGrammar(),
            default  => new MySqlGrammar(), // mysql, mariadb
        };
    }
}

abstract class Grammar
{
    abstract public function compileCreate(Blueprint $blueprint): array;
    abstract public function compileAlter(Blueprint $blueprint): array;

    protected function wrap(string $value): string
    {
        return Schema::wrap($value);
    }

    protected function wrapTable(Blueprint $blueprint): string
    {
        return $this->wrap($blueprint->getTable());
    }
}

class MySqlGrammar extends Grammar
{
    public function compileCreate(Blueprint $blueprint): array
    {
        $columns = [];
        $primary = null;

        foreach ($blueprint->getCommands() as $command) {
            if ($command instanceof ColumnDefinition) {
                $colSql = $this->compileColumn($command);

                if (\in_array('primary', $command->modifiers ?? [], true) || ($command->name === 'id' && !isset($primary))) {
                    $primary = $command->name;
                }

                $columns[] = $colSql;
            }
        }

        $sql = "CREATE TABLE {$this->wrapTable($blueprint)} (" . implode(', ', $columns);

        if ($primary) {
            $sql .= ", PRIMARY KEY (" . $this->wrap($primary) . ")";
        }

        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $statements = [$sql];

        // Add indexes
        $statements = \array_merge($statements, $this->compileIndexes($blueprint));

        return $statements;
    }

    public function compileAlter(Blueprint $blueprint): array
    {
        $statements = [];

        foreach ($blueprint->getCommands() as $command) {
            if ($command instanceof ColumnDefinition) {
                $colSql = $this->compileColumn($command, true);
                $statements[] = "ALTER TABLE {$this->wrapTable($blueprint)} ADD COLUMN {$colSql}";
            }
        }

        $statements = array_merge($statements, $this->compileIndexes($blueprint));

        return $statements;
    }

    protected function compileColumn(ColumnDefinition $column, bool $isAlter = false): string
    {
        $sql = $this->wrap($column->name) . ' ' . $column->type;

        if (!empty($column->modifiers['unsigned'] ?? false)) {
            $sql .= ' UNSIGNED';
        }

        $nullable = $column->modifiers['nullable'] ?? false;
        $sql .= $nullable ? ' NULL' : ' NOT NULL';

        if (isset($column->modifiers['default'])) {
            $default = $column->modifiers['default'];
            if ($default === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } elseif (is_numeric($default)) {
                $sql .= " DEFAULT {$default}";
            } else {
                $sql .= " DEFAULT '" . addslashes($default) . "'";
            }
        }

        if(\in_array('autoincrement', $column->modifiers, 1)) {
            $sql .= ' AUTO_INCREMENT';
        }

        if (!empty($column->modifiers['on_update_current_timestamp'] ?? false)) {
            $sql .= " ON UPDATE CURRENT_TIMESTAMP";
        }

        if (!empty($column->modifiers['unique'] ?? false)) {
            $sql .= ' UNIQUE';
        }

        return $sql;
    }

    protected function compileIndexes(Blueprint $blueprint): array
    {
        $statements = [];
        foreach ($blueprint->getCommands() as $command) {
            if ($command instanceof IndexDefinition) {
                $cols = is_array($command->columns) 
                    ? implode(', ', array_map($this->wrap(...), $command->columns))
                    : $this->wrap($command->columns);

                $indexName = $command->name ?? $blueprint->getTable() . '_' . 
                            (is_array($command->columns) ? implode('_', $command->columns) : $command->columns) . '_index';

                $type = $command->type === 'unique' ? 'UNIQUE INDEX' : 'INDEX';

                $statements[] = "CREATE {$type} {$this->wrap($indexName)} ON {$this->wrapTable($blueprint)} ({$cols})";
            }
        }
        return $statements;
    }
}

class SqliteGrammar extends MySqlGrammar
{
    public function compileCreate(Blueprint $blueprint): array
    {
        $sql = parent::compileCreate($blueprint);

        return $sql;
    }

    protected function compileColumn(ColumnDefinition $column, bool $isAlter = false): string
    {
        $sql = parent::compileColumn($column, $isAlter);

        $sql .= str_replace(' UNSIGNED', '', $sql);
        $sql .= str_replace(' AUTO_INCREMENT', ' AUTOINCREMENT', $sql);

        return $sql;
    }
}