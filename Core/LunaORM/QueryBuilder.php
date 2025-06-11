<?php

namespace Core\LunaORM;

use PDO;

class QueryBuilder
{
    protected $pdo;
    protected $table;
    protected $selects = ['*'];
    protected $wheres = [];
    protected $bindings = [];
    protected $limit = null;
    protected $offset = null;
    protected $order = [];

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function select(array $columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->order[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = $value;
        return $this;
    }

    public function offset(int $value): self
    {
        $this->offset = $value;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit = 1;
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => "?", $columns);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function update(array $data): bool
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = ?";
            $this->bindings[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    protected function buildSelect(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(', ', $this->order);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }
}
