<?php

namespace Core\Console\Commands;

use Core\Console\Command;

class MakeCommandCommand extends Command
{
    public string $signature = 'make:command';
    public string $description = 'Create console command';

    public function handle(array $args): void
    {
        $name = $args[0] ?? null;

        if(!$name) {
            $this->error('Command name is required', true);
            return;
        }

        $path = __DIR__ . '/../../../app/Console/' . $name . '.php';

        if(!is_dir(__DIR__ . '/../../../app/Console')) {
            mkdir(__DIR__ . '/../../../app/Console', 0755);
        }

        if(file_exists($path)) {
            $this->error('Command already exists', true);
            return;
        }
        $signature = $this->makeSignatureFromClassName($name);
        $template = <<<PHP
<?php

namespace App\Console;

use Core\Console\Command;

class {$name} extends Command
{
    public string \$signature = "{$signature}";
    public string \$description = '';

    public function handle(array \$args): void
    {
        //
    }
}
PHP;
        file_put_contents($path, $template);
        $this->info("Console command app/Console/{$name}.php created.", true);
    }


    private function makeSignatureFromClassName(string $name): string
    {
        $name_splitted = preg_split('/(?=[A-Z])/', $name);
        return strtolower(implode('-', array_filter($name_splitted)));
    }
}