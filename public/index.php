<?php
session_start(); // 🔴 Esto es obligatorio para usar $_SESSION
// Autocarga de clases
require_once __DIR__ . '/../core/autoload.php';


// Activar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Obtener solo la ruta relativa limpia desde la URL
$url = isset($_GET['url']) ? $_GET['url'] : 'home/index';

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
