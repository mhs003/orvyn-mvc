<?php

namespace App\Console;

use Core\Console\Command;

class TestCommand extends Command
{
    public string $signature = "test-command";
    public string $description = '';

    public function handle(array $args): void
    {
        //
    }
}