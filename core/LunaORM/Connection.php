<?php

namespace Core\LunaORM;

use Exception;
use PDO;

class Connection
{
    protected PDO $pdo;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if($config['enable']) $this->connect();
    }

    protected function connect(): void
    {
        $driver = $this->config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $this->config['database'];
            $this->pdo = new PDO($dsn);
        } else if($driver === 'mysql') {
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

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}