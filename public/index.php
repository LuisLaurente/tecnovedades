<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar opciones seguras para las cookies de sesión
// IMPORTANTE: Estas configuraciones deben establecerse ANTES de session_start()
ini_set('session.cookie_httponly', '1'); // Previene acceso a la cookie desde JavaScript
ini_set('session.use_only_cookies', '1'); // Solo usa cookies para mantener la sesión
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF en navegadores modernos
ini_set('session.use_strict_mode', '1'); // Modo estricto para prevenir session fixation

// En un entorno de producción, también habilitaríamos cookies seguras (HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

// Asegurarnos de que las configuraciones se apliquen
session_start(); 

// Autocarga de Composer (¡IMPORTANTE!)
require_once __DIR__ . '/../vendor/autoload.php';

// Autocarga de clases propias
require_once __DIR__ . '/../Core/autoload.php';

// Incluir Helpers manuales
require_once __DIR__ . '/../Core/Helpers/urlHelper.php';
require_once __DIR__ . '/../Core/Helpers/Sanitizer.php';
require_once __DIR__ . '/../Core/Helpers/SessionHelper.php';
require_once __DIR__ . '/../Core/Helpers/AuthMiddleware.php';
require_once __DIR__ . '/../Core/Helpers/Validator.php';
require_once __DIR__ . '/../Core/Helpers/CsrfHelper.php';

// Activar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener solo la ruta relativa limpia desde la URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

if ($url === '') {
    $url = 'home/index'; // Si no hay URL, va al home público
}

// ✅ APLICAR MIDDLEWARE DE AUTENTICACIÓN ANTES DEL ROUTING
\Core\Helpers\AuthMiddleware::checkAuth($url);

// Inyectar en $_GET['url'] para compatibilidad con el router
$_GET['url'] = $url;

// Iniciar el router
use Core\Router;

$router = new Router();
$router->handleRequest($url);
