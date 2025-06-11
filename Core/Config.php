<?php

namespace Core;

// use Exception;

class Config
{
    protected array $files = [];
    protected array $loaded = [];

    public function __construct()
    {
        $this->files = [
            'app' => __DIR__ . '/../config/app.php',
            'database' => __DIR__ . '/../config/database.php',
        ];
    }

    public function init(): void
    {
        foreach ($this->files as $key => $path) {
            $this->loaded[$key] = $this->loadFile($path);
        }
    }

    protected function loadFile(string $path): array
    {
        if (!file_exists($path)) {
            ddb("Config file not found: {$path}");
        }

        $config = require $path;

        if (!is_array($config)) {
            ddb("Config file must return an array: {$path}");
        }

        return $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->loaded;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
