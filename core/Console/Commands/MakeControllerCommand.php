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

        if (!$name) {
            $this->error('Controller name is required', true);
            return;
        }

        $classBaseNamespaces = $this->extractClassBaseNamespace($name);
        $classBaseName = $classBaseNamespaces[1];
        $className = "{$classBaseName}Controller";
        $baseFilepath = (\count($classBaseNamespaces[0]) > 0 ? implode('/', $classBaseNamespaces[0]) . '/' : '') . $className . '.php';
        $path = __DIR__ . '/../../../app/Controllers/' . $baseFilepath;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755);
        }

        if (file_exists($path)) {
            $this->error('Controller already exists', true);
            return;
        }

        $namespace_postfix = \count($classBaseNamespaces[0]) > 0 ? '\\' . implode('\\', $classBaseNamespaces[0]) : '';
        $route_path = str_replace('\\', '/', $namespace_postfix) . '/' . $classBaseName;

        $template = <<<PHP
<?php

namespace App\Controllers{$namespace_postfix};

use Attributes\Route;
use Attributes\DefaultRoute;
use Core\Http\Request;
use Core\Http\Response;

#[Route('{$route_path}')]
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
        $this->info("Controller app/Controllers/{$baseFilepath} created.", true);
    }

    private function extractClassBaseNamespace(string $name): array
    {
        if (str_ends_with(strtolower($name), 'controller')) {
            $name = substr($name, 0, \strlen($name) - 10);
        }
        $namespace_extracted = explode('/', $name);
        $name = ucfirst(array_pop($namespace_extracted));
        return [$namespace_extracted, $name];
    }
}