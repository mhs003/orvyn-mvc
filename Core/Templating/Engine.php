<?php
namespace Core\Templating;

use Core\Templating\Exceptions\TemplateException;
use App\Views\Layouts;

class Engine
{
    protected $compiler;
    protected $layouts = [];
    protected $currentSection = null;
    protected $layoutsRegistry = [];
    protected $yieldContent = [];
    protected $layoutToUse = null;

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
        $this->layoutsRegistry = Layouts::register();
        Directive::extendBuiltins($compiler); // Register built-ins
    }

    public function render(string $template, array $data = []): string
    {
        extract($data);

        // Check if compilation is needed
        $compiledFile = $this->compiler->compile($template);

        ob_start();
        try {
            include $compiledFile;
            if ($this->layoutToUse) {
                echo $this->renderLayout($this->layoutToUse);
                $this->layoutToUse = null;
            }
        } catch (\Throwable $e) {
            // Map the error back to the original template
            $this->handleTemplateError($e, $compiledFile);
        }
        return ob_get_clean();
    }

    protected function handleTemplateError(\Throwable $e, string $compiledFile): void
    {
        // Extract the original template and line number from the compiled file
        $errorLine = $e->getLine();
        $compiledContent = file($compiledFile);

        $template = null;
        $lineNumber = null;

        // Look for the line comment in the compiled file
        for ($i = max(0, $errorLine - 5); $i < min(count($compiledContent), $errorLine + 5); $i++) {
            if (preg_match('/<\?php \/\* Template: (.+), Line: (\d+) \*\/ \?>/', $compiledContent[$i], $matches)) {
                $template = $matches[1];
                $lineNumber = $matches[2];
                break;
            }
        }

        // Throw a more descriptive exception
        throw new TemplateException($e->getMessage(), $template, $lineNumber);
    }

    public function startSection(string $name): void
    {
        // Parse section name (e.g., 'base/content' -> registry: 'base', yield: 'content')
        if (strpos($name, '/') !== false) {
            [$registryName, $yieldName] = explode('/', $name, 2);

            if (!isset($this->layoutsRegistry[$registryName])) {
                throw new TemplateException("Section registry '{$registryName}' not found.");
            }

            $this->currentSection = ['registry' => $registryName, 'yield' => $yieldName, 'name' => $name];
        } else {
            // Regular section without registry
            $this->currentSection = ['name' => $name];
        }

        ob_start(); // Start output buffering
    }

    public function endSection(): void
    {
        if (!$this->currentSection) {
            throw new TemplateException("No section started.");
        }

        $content = ob_get_clean(); // Get buffered content
        $sectionName = $this->currentSection['name'];

        // Store the content
        $this->layouts[$sectionName] = $content;

        $this->currentSection = null; // Reset current section
    }

    public function yieldContent(string $name): string
    {
        return $this->yieldContent[$name] ?? $this->layouts[$name] ?? ''; // Return section content or empty string
    }

    protected function renderLayout(string $layoutFile): string
    {
        $layoutPath = $this->compiler->path . '/' . $layoutFile;

        if (!file_exists($layoutPath)) {
            throw new TemplateException("Layout file not found: {$layoutFile}");
        }

        // Compile the layout file
        $layoutTemplate = str_replace(['/', '.php'], ['.', ''], $layoutFile);
        $compiledLayoutFile = $this->compiler->compile($layoutTemplate);

        ob_start();
        try {
            include $compiledLayoutFile;
        } catch (\Throwable $e) {
            $this->handleTemplateError($e, $compiledLayoutFile);
        }

        $result = ob_get_clean();

        // Clear yield content after rendering
        $this->yieldContent = [];

        return $result;
    }

    public function useLayout(string $layoutName): void
    {
        if (!isset($this->layoutsRegistry[$layoutName])) {
            throw new TemplateException("Layout '{$layoutName}' not found in section registry.");
        }

        $this->layoutToUse = $this->layoutsRegistry[$layoutName];
    }
}