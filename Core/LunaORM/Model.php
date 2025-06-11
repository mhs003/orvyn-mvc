<?php

namespace Core\LunaORM;

use PDO;
use ReflectionClass;

abstract class Model
{
    protected static PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->table = $this->getTableName();
    }

    public static function setConnection(PDO $pdo): void
    {
        static::$connection = $pdo;
    }

    public static function getConnection(): PDO
    {
        return static::$connection;
    }

    protected function getTableName(): string
    {
        return $this->table ?? strtolower((new ReflectionClass(static::class))->getShortName()) . 's';
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        return new QueryBuilder(static::$connection, $instance->getTableName());
    }

    public static function all(): array
    {
        return array_map(fn($row) => new static($row), static::query()->get());
    }

    public static function find($id): ?static
    {
        $instance = new static();
        $row = static::query()->where($instance->primaryKey, '=', $id)->first();
        return $row ? new static($row) : null;
    }

    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public function save(): bool
    {
        return static::query()->insert($this->attributes);
    }

    public function update(array $data): bool
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id)
            return false;

        return static::query()
            ->where($this->primaryKey, '=', $id)
            ->update($data);
    }

    public function delete(): bool
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id)
            return false;

        return static::query()
            ->where($this->primaryKey, '=', $id)
            ->delete();
    }
}
