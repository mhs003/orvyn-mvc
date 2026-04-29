<?php

namespace Core;

class Config
{
    protected array $loaded = [];

    public function __construct()
    {
        // 
    }

    public function init(): void
    {
        $path = __DIR__ . '/../config/*.php';
        $files = glob($path);
        foreach ($files as $file) {
            $fname = basename($file);
            $key = explode('.', $fname)[0];
            $this->loaded[$key] = $this->loadFile($file);
        }
    }

    protected function loadFile(string $path): array
    {
        if (!file_exists($path)) {
            die("Config file not found: {$path}");
        }

        $config = require $path;

        if (!\is_array($config)) {
            die("Config file must return an array: {$path}");
        }

        return $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->loaded;

        foreach ($segments as $segment) {
            if (!\is_array($value) || !\array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}