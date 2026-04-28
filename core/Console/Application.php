<?php

namespace Core\Console;

class Application
{
    protected Kernel $kernel;

    public function __construct()
    {
        $this->kernel = new Kernel();
    }

    public function run(array $argv)
    {
        $command = $argv[1] ?? null;
        
        if(!$command) {
            echo "No command provided\n";
            return;
        }

        $this->kernel->handle($command, \array_slice($argv, 2));
    }
}