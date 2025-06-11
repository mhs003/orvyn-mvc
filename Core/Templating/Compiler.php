<?php
namespace Core\Templating;

use Core\Templating\Exceptions\TemplateException;

class Compiler {
    public $path;
    protected $compiledPath;
    protected $directives = [];

    public function __construct(string $path, string $compiledPath) {
        $this->path = $path;

        if (!is_dir($compiledPath)) mkdir($compiledPath, 0755);

        $this->compiledPath = $compiledPath;
        $this->directives = Directive::getDirectives();
    }

    public function registerDirective(string $name, callable $handler): void {
        $this->directives[$name] = $handler;
    }

    public function compile(string $template): string {
        $templatePath = str_replace('.', '/', $template);
        $sourceFile = "{$this->path}/{$templatePath}.php";
        $compiledFile = "{$this->compiledPath}/" . md5($template) . ".php";

        // Check if source file exists
        if (!file_exists($sourceFile)) {
            throw new TemplateException("Template not found: {$template}");
        }

        // Recompile only if source is newer than compiled file
        if (!file_exists($compiledFile) || filemtime($sourceFile) > filemtime($compiledFile)) {
            $content = file_get_contents($sourceFile);

            // Process directives and add line numbers for error tracking
            $content = $this->compileStatements($content);
            $content = $this->injectLineComments($content, $sourceFile);
            $content = $this->processCustomDirectives($content);

            file_put_contents($compiledFile, $content);
        }

        return $compiledFile;
    }

    protected function compileStatements(string $content): string {
        // Built-in directives (e.g., @if, @foreach)
        $patterns = [
            '/@if\s*\((.*)\)/' => '<?php if($1): ?>',
            '/@elseif\s*\((.*)\)/' => '<?php elseif($1): ?>',
            '/@else/' => '<?php else: ?>',
            '/@endif/' => '<?php endif; ?>',
            '/@foreach\s*\((.*)\)/' => '<?php foreach($1): ?>',
            '/@endforeach/' => '<?php endforeach; ?>',
            '/@include\s*\(\s*[\'"](.+?)[\'"]\s*\)/' => '<?php include $this->render("$1"); ?>',
            '/{{(.+?)}}/' => '<?php echo htmlspecialchars($1, ENT_QUOTES); ?>',
            '/{!!(.+?)!!}/' => '<?php echo $1; ?>',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $content);
    }

    protected function injectLineComments(string $content, string $sourceFile): string {
        $lines = explode("\n", $content);
        $compiledContent = '';
    
        foreach ($lines as $lineNumber => $line) {
            // Add a comment with the original file and line number
            $compiledContent .= "<?php /* Template: {$sourceFile}, Line: " . ($lineNumber + 1) . " */ ?>\n";
            $compiledContent .= "{$line}\n";
        }
    
        return $compiledContent;
    }

    protected function processCustomDirectives(string $content): string {
        foreach ($this->directives as $name => $handler) {
            $pattern = "/\B@{$name}([ \t]*)(\( ( (?>[^()]+) | (?2) )* \))?/x";
            $content = preg_replace_callback($pattern, function ($matches) use ($handler) {
                return $handler($matches[2] ?? '');
            }, $content);
        }

        return $content;
    }
}