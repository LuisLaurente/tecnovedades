<?php
// Aplicar las configuraciones de seguridad
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

// Iniciar la sesión
session_start();

// Mostrar información de configuración
echo "<h1>Configuración de Sesiones PHP</h1>";
echo "<h2>Versión de PHP: " . phpversion() . "</h2>";

// Tabla para configuraciones de sesión
echo "<h3>Configuraciones de Sesión:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Configuración</th><th>Valor</th></tr>";

$sessionSettings = [
    'session.use_strict_mode',
    'session.use_only_cookies',
    'session.cookie_httponly',
    'session.cookie_samesite',
    'session.cookie_secure'
];

foreach ($sessionSettings as $setting) {
    echo "<tr>";
    echo "<td>{$setting}</td>";
    echo "<td>" . (ini_get($setting) ?: "(vacío)") . "</td>";
    echo "</tr>";
}

echo "</table>";

// Mostrar información completa de PHP
echo "<hr><h3>PHP Info Completa:</h3>";
phpinfo();
