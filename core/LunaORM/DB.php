<?php

namespace Core\LunaORM;

use Exception;

class DB
{
    protected static ?Connection $connection = null;
    protected static array $config = [];

    public static function init(): void
    {
        self::$config = config('database');
    }

    public static function connection(): Connection
    {
        if(self::$connection === null) {
            $default = self::$config['default'] ?? 'sqlite';
            $connections = self::$config['connections'] ?? [];
            $config = $connections[$default] ?? throw new Exception("Database connection '{$default}' not configured");
            self::$connection = new Connection([
                'enable' => self::$config['enable'] ?: false,
                ...$config
            ]);
        }
        return self::$connection;
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::connection()->$method(...$args);
    }
}