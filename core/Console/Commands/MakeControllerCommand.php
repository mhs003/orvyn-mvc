<?php

namespace Core\Console\Commands;

use Core\Console\Command;

class MakeControllerCommand extends Command
{
    public string $signature = 'make:controller';
    public string $description = 'Create a new controller';

    public function handle(array $args): void
    {
        $name = $args[0] ?? null;

        if(!$name) {
            $this->error('Controller name is required');
            return;
        }

        $classBaseName = $this->extractClassBaseName($name);
        $className = "{$classBaseName}Controller";
        $path = __DIR__ . '/../../../app/Controllers/' . $className . '.php';

        if(file_exists($path)) {
            $this->error('Controller already exists');
            return;
        }

        $template = <<<PHP
<?php

namespace App\Controllers;

use Attributes\DefaultRoute;
use Core\Http\Request;
use Core\Http\Response;
use Attributes\Route;

#[Route('/{$classBaseName}')]
class {$className}
{
    #[DefaultRoute]
    public function index(Request \$request)
    {
        return Response::make("Hello From {$className}");
    }
}
PHP;

        file_put_contents($path, $template);
        $this->info("Controller app/Controllers/{$className}.php created.");
    }

    private function extractClassBaseName(string $name): string {
        if(str_ends_with(strtolower($name), 'controller')) {
            $name = substr($name, 0, \strlen($name) - 10);
        }
        return ucfirst($name);
    }
}