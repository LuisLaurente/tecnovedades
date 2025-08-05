<?php
// Detectar entorno automáticamente
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8080']) 
                && !str_contains($_SERVER['HTTP_HOST'], 'xampp');

if ($isProduction) {
    // Configuración para producción (cPanel)
    return [
        'host' => '148.113.206.59',
        'dbname' => 'decorpie_tecnovedades',  // CAMBIAR: por tu base de datos
        'username' => 'decorpie_tecnouser',         // CAMBIAR: por tu usuario de BD
        'password' => 'tecnouser123CJSL',            // CAMBIAR: por tu contraseña
        'port' => 3306
    ];
} else {
    // Configuración para desarrollo local
    return [
        'host' => '127.0.0.1',    // 'host' => '127.0.0.1',
        'dbname' => 'tecnovedades', // 'dbname' => 'tecnovedades',
        'username' => 'root',   // 'username' => 'root
        'password' => 'root', // 'password' => '',
        'port' => 3306
    ];
}
