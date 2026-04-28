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
        $input = new Input($argv);
        
        if(!$input->command) {
            echo "No command provided\n";
            return;
        }

        $this->kernel->handle($input);
    }
}