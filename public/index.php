<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); 

// Autocarga de clases
require_once __DIR__ . '/../Core/autoload.php';

// Incluir helpers manuales
require_once __DIR__ . '/../Core/helpers/urlHelper.php';
require_once __DIR__ . '/../Core/helpers/Sanitizer.php';
require_once __DIR__ . '/../Core/helpers/SessionHelper.php';
require_once __DIR__ . '/../Core/helpers/AuthMiddleware.php';
require_once __DIR__ . '/../Core/helpers/Validator.php';

// Activar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener solo la ruta relativa limpia desde la URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Si está vacía, redirigir según el estado de autenticación
if ($url === '' || $url === 'home/index') {
    if (\Core\Helpers\SessionHelper::isAuthenticated()) {
        $url = 'auth/profile';
    } else {
        $url = 'auth/login';
    }
}

// Inyectar en $_GET['url'] para compatibilidad con el router
$_GET['url'] = $url;

// Iniciar el router
use Core\Router;

$router = new Router();
$router->handleRequest($url);
