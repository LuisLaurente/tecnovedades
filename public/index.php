<?php

// Activar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autocarga de clases
require_once __DIR__ . '/../core/autoload.php';

// Capturar la URL manualmente si no hay .htaccess (compatible con PHP -S)
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Limpiar path
$url = str_replace($scriptName, '', $requestUri);
$url = trim($url, '/');

// Separar la URL de los parámetros GET
$urlParts = parse_url($url);
$url = $urlParts['path'] ?? '';

// Si está vacía, usar ruta por defecto 2
if ($url === '') {
    $url = 'home/index';
}

// Inyectar en $_GET['url'] para compatibilidad con el router
$_GET['url'] = $url;

// Iniciar el router
use Core\Router;

$router = new Router();
$router->handleRequest($url);
