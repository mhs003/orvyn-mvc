<?php

use Core\Templating\Directive;

// Custom directives

Directive::register('peko', function (string $expression): string {
    return "<?php echo {$expression}; ?>";
});

Directive::register('now', function (): string {
    return "<?php echo date('Y-m-d H:i:s'); ?>";
});