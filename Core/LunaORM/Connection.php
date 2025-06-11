<?php

namespace Core\LunaORM;

use PDO;
use PDOException;

class Connection
{
    protected static $pdo;

    public static function connect(): void
    {
        $driver = config('database.usedriver');
        $host = config("database.{$driver}.host") ?? '127.0.0.1';
        $port = config("database.{$driver}.port");
        $database = config("database.{$driver}.database");
        $username = config("database.{$driver}.username");
        $password = config("database.{$driver}.password");
        $charset = config("database.{$driver}.charset") ?? 'utf8mb4';

        try {
            switch ($driver) {
                case 'sqlite':
                    self::$pdo = new PDO("sqlite:{$database}");
                    break;

                case 'mysql':
                    $port = $port ?? 3306;
                    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
                    self::$pdo = new PDO($dsn, $username, $password);
                    break;

                default:
                    ddb("Unsupported driver: {$driver}");
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            ddb("DB Connection failed: " . $e->getMessage());
        }
    }

    public static function getPDO(): PDO
    {
        if (!self::$pdo) {
            ddb("No database connection. Call connect() first.");
        }
        return self::$pdo;
    }
}
