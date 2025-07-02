<?php
// Cargar autoload
require_once '../core/autoload.php';

use Core\Router;

// Ejecutar enrutador
$router = new Router();
$router->run();
