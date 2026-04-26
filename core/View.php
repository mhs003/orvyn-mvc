<?php

namespace Core;

class View
{
    public static function render(string $file, array $data = [])
    {
        // $file_path = __DIR__ . '/../app/Views/' . $file . '.tpl.php';

        ob_start();
        extract($data);
        include $file;
        $content = ob_get_clean();

        return $content;
    }
}
