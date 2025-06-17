<?php

namespace Core\LunaORM;

use PDO;
use ReflectionClass;
use Exception;

abstract class Model
{
    protected static PDO $connection;

    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];

    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];


    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
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

    public static function query(): QueryBuilder
    {
        $instance = new static();
        return new QueryBuilder(static::$connection, $instance->getTableName());
    }

    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function toArray(): array
    {
        return array_filter($this->attributes, fn($key) => !in_array($key, $this->hidden), ARRAY_FILTER_USE_KEY);
    }

    public function __get($key)
    {
        $value = $this->attributes[$key] ?? null;

        if ($value !== null && isset($this->casts[$key])) {
            switch ($this->casts[$key]) {
                case 'int':
                    return (int) $value;
                case 'float':
                    return (float) $value;
                case 'bool':
                case 'boolean':
                    return (bool) $value;
                case 'array':
                case 'json':
                    return is_string($value) ? json_decode($value, true) : $value;
                case 'datetime':
                    return new \DateTime($value);
                default:
                    return $value;
            }
        }

        return $value;
    }

    public function __set($key, $value)
    {
        if (isset($this->casts[$key])) {
            switch ($this->casts[$key]) {
                case 'json':
                case 'array':
                    $this->attributes[$key] = json_encode($value);
                    return;
                case 'datetime':
                    $this->attributes[$key] = is_string($value) ? $value : $value->format('Y-m-d H:i:s');
                    return;
            }
        }

        $this->attributes[$key] = $value;
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

    public static function first(): ?static
    {
        $row = static::query()->limit(1)->first();
        return $row ? new static($row) : null;
    }

    public static function firstOrFail(): static
    {
        $result = static::first();
        if (!$result) {
            throw new Exception(static::class . " not found.");
        }
        return $result;
    }

    public static function pluck(string $column): array
    {
        return array_map(fn($row) => $row[$column] ?? null, static::query()->select([$column])->get());
    }

    public static function exists(): bool
    {
        return count(static::query()->limit(1)->get()) > 0;
    }

    public static function count(): int
    {
        $results = static::query()->select(['COUNT(*) AS count'])->get();
        return (int) ($results[0]['count'] ?? 0);
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
