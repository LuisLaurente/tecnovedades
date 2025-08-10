<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); 

// Autocarga de clases
require_once __DIR__ . '/../Core/autoload.php';

// Incluir Helpers manuales
require_once __DIR__ . '/../Core/Helpers/urlHelper.php';
require_once __DIR__ . '/../Core/Helpers/Sanitizer.php';
require_once __DIR__ . '/../Core/Helpers/SessionHelper.php';
require_once __DIR__ . '/../Core/Helpers/AuthMiddleware.php';
require_once __DIR__ . '/../Core/Helpers/Validator.php';

// Activar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener solo la ruta relativa limpia desde la URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

if ($url === '' || $url === 'home/index') {
    if (\Core\Helpers\SessionHelper::isAuthenticated()) {
        $url = 'auth/profile'; // Si está logueado, va a su perfil
    } else {
        $url = 'home/index'; // Si no está logueado, va al home público
    }
}

// ✅ APLICAR MIDDLEWARE DE AUTENTICACIÓN ANTES DEL ROUTING
\Core\Helpers\AuthMiddleware::checkAuth($url);

// Inyectar en $_GET['url'] para compatibilidad con el router
$_GET['url'] = $url;

// Iniciar el router
use Core\Router;

$router = new Router();
$router->handleRequest($url);
