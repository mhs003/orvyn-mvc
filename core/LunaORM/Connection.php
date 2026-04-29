<?php

namespace Core\LunaORM;

use Exception;
use PDO;
use PDOStatement;

class Connection
{
    protected PDO $pdo;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if ($config['enable'])
            $this->connect();
    }

    protected function connect(): void
    {
        $driver = $this->config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $this->config['database'];
            $this->pdo = new PDO($dsn);
        } else if ($driver === 'mysql') {
            $dsn = \sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $driver,
                $this->config['host'] ?? '127.0.0.1',
                $this->config['port'] ?? 3306,
                $this->config['database'],
                $this->config['charset'] ?? 'utf8mb4',
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'] ?? '',
                $this->config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } else {
            throw new Exception("Database driver '{$driver}' not implemented yet");
        }

        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    public function insert(string $sql, array $bindings = []): int
    {
        $this->query($sql, $bindings);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    public function delete(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction() : bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}