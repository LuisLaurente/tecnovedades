<?php
spl_autoload_register(function($class) {
    $baseDir = __DIR__ . '/../';
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Cargar helpers globales
require_once __DIR__ . '/helpers/producto_helpers.php';
