<?php

namespace Core\Console;

use Core\Console\Commands\MakeControllerCommand;
use Core\Console\Commands\ServeCommand;

class Kernel
{
    protected array $commands = [
        MakeControllerCommand::class,
        ServeCommand::class,
    ];

    public function handle(string $name, array $args)
    {
        foreach($this->commands as $commandClass) {
            $command = new $commandClass;

            if($name === $command->signature) {
                return $command->handle($args);
            }
        }

        echo "Command not found {$name}\n";
    }
}