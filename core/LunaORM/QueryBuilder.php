<?php

namespace Core\LunaORM;

class QueryBuilder
{
    protected Connection $connection;
    protected string $table;
    protected array $wheres = [];
    protected array $bindings = [];
    protected ?string $orderBy = null;
    protected ?string $orderDir = null;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $selects = ['*'];

    public function __construct(string $table, Connection $connection)
    {
        $this->table = $table;
        $this->connection = $connection;
    }

    public function select(array $columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    public function where(string $column, string|int|null $operator = null, mixed $value = null): self
    {
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        $operator = $operator === null ? '=' : $operator;
        $this->wheres[] = ["{$column} {$operator} ?", $value];
        return $this;
    }

    public function orWhere(string $column, string|int|null $operator = null, mixed $value = null): self
    {
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        $operator = $operator === null ? '=' : $operator;
        $this->wheres[] = ["{$column} {$operator} ?", $value, 'OR'];
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = ["{$column} IN ({$placeholders})", $values];
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = ["{$column} IS NULL", null];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = ["{$column} IS NOT NULL", null];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = $column;
        $this->orderDir = strtoupper($direction);
        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = $value;
        return $this;
    }

    public function take(int $value): self
    {
        return $this->limit($value);
    }

    public function offset(int $value): self
    {
        $this->offset = $value;
        return $this;
    }

    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    public function get(): array
    {
        [$sql, $bindings] = $this->compileSelect();
        return $this->connection->select($sql, $bindings);
    }

    public function first(): ?array
    {
        $result = $this->limit(1)->get();
        return $result[0] ?? null;
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        [$sql, $bindings] = $this->compileSelect();
        $result = $this->connection->select($sql, $bindings);
        return (int) ($result[0]['count'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            $placeholders
        );

        return $this->connection->insert($sql, array_values($data));
    }

    public function update(array $data): int
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        [$whereSql, $whereBindings] = $this->compileWheres();

        $sql = "UPDATE {$this->table} SET {$set}";
        if ($whereSql) {
            $sql .= " WHERE {$whereSql}";
        }

        return $this->connection->update($sql, [...array_values($data), ...$whereBindings]);
    }

    public function delete(): int
    {
        [$whereSql, $whereBindings] = $this->compileWheres();

        $sql = "DELETE FROM {$this->table}";
        if ($whereSql) {
            $sql .= " WHERE {$whereSql}";
        }

        return $this->connection->delete($sql, $whereBindings);
    }

    protected function compileSelect(): array
    {
        $sql = sprintf('SELECT %s FROM %s', implode(', ', $this->selects), $this->table);
        [$whereSql, $bindings] = $this->compileWheres();

        if ($whereSql) {
            $sql .= " WHERE {$whereSql}";
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy} {$this->orderDir}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return [$sql, $bindings];
    }

    protected function compileWheres(): array
    {
        if (empty($this->wheres)) {
            return ['', []];
        }

        $sqlParts = [];
        $bindings = [];

        foreach ($this->wheres as $i => $where) {
            $condition = $where[0];
            $value = $where[1] ?? null;
            $boolean = $where[2] ?? 'AND';

            if ($i > 0) {
                $sqlParts[] = $boolean;
            }

            $sqlParts[] = $condition;

            if ($value !== null) {
                if (is_array($value)) {
                    $bindings = array_merge($bindings, $value);
                } else {
                    $bindings[] = $value;
                }
            }
        }

        return [implode(' ', $sqlParts), $bindings];
    }
}
