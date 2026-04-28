<?php

namespace Core\Console;

class Kernel
{
    protected array $commands = [];

    public function __construct()
    {
        $this->loadCommandsFrom(__DIR__ . '/Commands', 'Core\\Console\\Commands');
        $this->loadCommandsFrom(__DIR__ . '/../../app/Console', 'App\\Console');
    }

    public function handle(string $name, array $args)
    {
        foreach ($this->commands as $commandClass) {
            $command = new $commandClass;

            if ($name === $command->signature) {
                return $command->handle($args);
            }
        }

        echo "Command not found {$name}\n";
    }

    protected function loadCommandsFrom(string $path, string $namespace)
    {
        foreach (glob("{$path}/*.php") as $file) {
            $class = $namespace . '\\' . basename($file, '.php');

            if (class_exists($class)) {
                $this->commands[] = $class;
            }
        }
    }
}