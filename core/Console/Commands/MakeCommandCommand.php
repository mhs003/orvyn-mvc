<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Console\Input;

class MakeCommandCommand extends Command
{
    public string $signature = 'make:command';
    public string $description = 'Create console command';

    public function handle(Input $input): void
    {
        $name = $input->arg(0);

        if(!$name) {
            $this->error('Command name is required', true);
            return;
        }

        $name = str_replace(['/', '\\'], '', $name);

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
use Core\Console\Input;

class {$name} extends Command
{
    public string \$signature = "{$signature}";
    public string \$description = '';

    public function handle(Input \$input): void
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