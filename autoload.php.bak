<?php
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/' . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
