<?php
namespace Core\Templating;

/**
 * Directive Class
 * Handles custom directives for the templating engine
 * 
 * @package Core\Templating
 */

class Directive
{
    protected static $directives = [];

    public static function register(string $name, callable $handler): void
    {
        self::$directives[$name] = $handler;
    }

    public static function getDirectives(): array
    {
        return self::$directives;
    }

    public static function extendBuiltins(Compiler $compiler): void
    {
        $compiler->registerDirective('section', function ($expression) {
            // Remove parentheses and quotes from expression
            $sectionName = trim($expression, "()\"'");
            return "<?php \$this->startSection('{$sectionName}'); ?>";
        });

        $compiler->registerDirective('endsection', function ($expression) {
            return "<?php \$this->endSection(); ?>";
        });

        $compiler->registerDirective('yield', function ($expression) {
            // Remove parentheses and quotes from expression
            $yieldName = trim($expression, "()\"'");
            return "<?php echo \$this->yieldContent('{$yieldName}'); ?>";
        });

        $compiler->registerDirective('uselayout', function ($expression) {
            $layoutName = trim($expression, "()\"'");
            return "<?php \$this->useLayout('{$layoutName}'); ?>";
        });
    }
}