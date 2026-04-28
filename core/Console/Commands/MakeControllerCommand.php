<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Console\Input;

class MakeControllerCommand extends Command
{
    public string $signature = 'make:controller';
    public string $description = 'Create a new controller';

    public function handle(Input $input): void
    {
        $name = $input->arg(0);

        if (!$name) {
            $this->error('Controller name is required', true);
            return;
        }

        $isApi = $input->hasOption('api');
        $isResource = $input->hasOption('resource');

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


        if($isResource) {
            if($isApi) {
                $templateBody = <<<PHP
    #[DefaultRoute]
    public function index(Request \$request)
    {
        //
    }
    
    #[Method('POST')]
    #[DefaultRoute]
    public function store(Request \$request)
    {
        //
    }

    #[Route('/:id')]
    public function view(Request \$request)
    {
        //
    }

    #[Method('PUT')]
    #[Route('/:id')]
    public function update(Request \$request)
    {
        //
    }

    #[Method('DELETE')]
    #[Route('/:id')]
    public function delete(Request \$request)
    {
        //
    }
PHP;
            } else {
                $templateBody = <<<PHP
    #[DefaultRoute]
    public function index(Request \$request)
    {
        //
    }
    
    #[Route('/create')]
    public function create(Request \$request)
    {
        //
    }
    
    #[Method('POST')]
    #[Route('/store')]
    public function store(Request \$request)
    {
        //
    }
    
    #[Route('/view/:id')]
    public function view(Request \$request)
    {
        //
    }

    #[Route('/edit/:id')]
    public function edit(Request \$request)
    {
        //
    }

    #[Method('PUT')]
    #[Route('/update/:id')]
    public function update(Request \$request)
    {
        //
    }

    #[Method('DELETE')]
    #[Route('/delete/:id')]
    public function delete(Request \$request)
    {
        //
    }
PHP;
            }
        } else {
            $templateBody = <<<PHP
    #[DefaultRoute]
    public function index(Request \$request)
    {
        //
    }
PHP;
        }

        $template = <<<PHP
<?php

namespace App\Controllers{$namespace_postfix};

use Attributes\Route;
use Attributes\DefaultRoute;
use Attributes\Method;
use Core\Http\Request;

#[Route('{$route_path}')]
class {$className}
{
{$templateBody}
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