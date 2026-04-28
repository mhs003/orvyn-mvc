<?php

namespace Core\Console;

class Input
{
    public string $command;
    public array $args = [];
    public array $options = [];

    public function __construct(array $argv)
    {
        $this->command = $argv[1] ?? '';

        foreach (\array_slice($argv, 2) as $arg) {

            // --key=value
            if (str_starts_with($arg, '--') && str_contains($arg, '=')) {
                [$key, $value] = explode('=', substr($arg, 2), 2);
                $this->options[$key] = $value;
            }

            // --flag
            elseif (str_starts_with($arg, '--')) {
                $this->options[substr($arg, 2)] = true;
            }

            // positional args
            else {
                $this->args[] = $arg;
            }
        }
    }

    public function arg(int $index, $default = null)
    {
        return $this->args[$index] ?? $default;
    }

    public function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption(string $key): bool
    {
        return \array_key_exists($key, $this->options);
    }
}