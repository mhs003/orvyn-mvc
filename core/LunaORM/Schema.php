<?php

namespace Core\LunaORM;

class Schema
{
    public static function driver(): string
    {
        return DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
    
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $blueprint->create();
        $callback($blueprint);
        $sql = $blueprint->toSql();
        DB::connection()->query($sql);
    }

    public static function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        if ($sql) {
            DB::connection()->query($sql);
        }
    }

    public static function drop(string $table): void
    {
        DB::connection()->query("DROP TABLE " . self::wrap($table));
    }

    public static function dropIfExists(string $table): void
    {
        DB::connection()->query("DROP TABLE IF EXISTS {$table}");
    }

    public static function hasTable(string $table): bool
    {
        try {
            DB::connection()->query(
                Schema::driver() === 'sqlite' ? "SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'" : "SHOW TABLES LIKE '{$table}'"
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected static function wrap(string $name): string
    {
        return Schema::driver() === 'sqlite' ? "\"{$name}\"" : "`{$name}`";
    }
}

class Blueprint
{
    protected string $table;
    protected bool $creating = false;
    protected array $columns = [];
    protected array $modifiers = [];
    protected array $indexes = [];

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
        $isSqlite = Schema::driver() === 'sqlite';

        if ($isSqlite) {
            return $this->addColumn('INTEGER', $column, ['PRIMARY KEY AUTOINCREMENT']);
        }

        return $this->addColumn('INT', $column, ['AUTO_INCREMENT', 'PRIMARY KEY']);
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
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    protected function addColumn(string $type, string $column, array $extra = []): ColumnDefinition
    {
        $definition = new ColumnDefinition($type, $column, $extra);
        $this->columns[] = $definition;
        return $definition;
    }

    public function index(string $column): void
    {
        $this->indexes[] = $column;
    }

    public function toSql(): string
    {
        $sql = $this->creating ? $this->compileCreate() : $this->compileAlter();

        foreach ($this->indexes as $column) {
            $sql .= "; CREATE INDEX {$this->table}_{$column}_index ON {$this->table} ({$column})";
        }

        return $sql;
    }

    protected function compileCreate(): string
    {
        $columns = [];

        foreach ($this->columns as $column) {
            $sql = "{$column->column} {$column->type}";
            if (!empty($column->extra)) {
                $sql .= ' ' . implode(' ', $column->extra);
            }
            if ($column->nullable) {
                $sql .= ' NULL';
            } else {
                $sql .= ' NOT NULL';
            }
            if ($column->default !== null) {
                if (is_numeric($column->default)) {
                    $sql .= " DEFAULT {$column->default}";
                } elseif (strtoupper($column->default) === 'CURRENT_TIMESTAMP') {
                    $sql .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $sql .= " DEFAULT '{$column->default}'";
                }
            }
            if (Schema::driver() === 'mysql') $sql .= "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $columns[] = $sql;
        }

        return \sprintf('CREATE TABLE %s (%s)', $this->table, implode(', ', $columns));
    }

    protected function compileAlter(): string
    {
        if (empty($this->columns)) {
            return '';
        }

        $columns = [];

        foreach ($this->columns as $column) {
            $sql = "ADD COLUMN {$column->column} {$column->type}";
            if (!empty($column->extra)) {
                $sql .= ' ' . implode(' ', $column->extra);
            }
            if ($column->nullable) {
                $sql .= ' NULL';
            } else {
                $sql .= ' NOT NULL';
            }
            if ($column->default !== null) {
                if (is_numeric($column->default)) {
                    $sql .= " DEFAULT {$column->default}";
                } elseif (strtoupper($column->default) === 'CURRENT_TIMESTAMP') {
                    $sql .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $sql .= " DEFAULT '{$column->default}'";
                }
            }
            $columns[] = $sql;
        }

        return sprintf('ALTER TABLE %s %s', $this->table, implode(', ', $columns));
    }
}

class ColumnDefinition
{
    public string $column;
    public string $type;
    public array $extra;
    public bool $nullable = false;
    public mixed $default = null;

    public function __construct(string $type, string $column, array $extra = [])
    {
        $this->type = $type;
        $this->column = $column;
        $this->extra = $extra;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    public function unique(): self
    {
        $this->extra[] = 'UNIQUE';
        return $this;
    }

    public function unsigned(): self
    {
        if (Schema::driver() !== 'sqlite') {
            $this->type = 'UNSIGNED ' . $this->type;
        }
        return $this;
    }
}
