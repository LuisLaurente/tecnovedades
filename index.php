<?php
// Autoload de clases
require_once __DIR__ . '/autoload.php';

// Cargar el router
require_once __DIR__ . '/core/Router.php';

use Core\Router;

// Obtener la ruta desde la URL
$url = $_GET['url'] ?? 'producto/index'; // ruta por defecto

$router = new Router();
$router->handleRequest($url);
