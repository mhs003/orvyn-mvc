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

    public function handle(Input $input)
    {
        foreach ($this->commands as $commandClass) {
            $command = new $commandClass;

            if ($input->command === $command->signature) {
                return $command->handle($input);
            }
        }

        echo "Command not found {$input->command}\n";
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