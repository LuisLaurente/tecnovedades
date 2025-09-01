<?php
namespace Core;
require_once __DIR__ . '/../controllers/BaseController.php';
class Router
{
    public function handleRequest($url)
    {
        $segments = explode('/', $url);

        $controllerName = ucfirst($segments[0] ?? 'home') . 'Controller';
        $methodName = $segments[1] ?? 'index';
        // Convertir google-callback a googleCallback
        $methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $methodName))));
        $params = array_slice($segments, 2);

        $controllerClass = 'Controllers\\' . $controllerName;
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($controllerClass)) {
        // Obtener instancia de DB
        $dbInstance = Database::getInstance()->getConnection();

        // Pasar al constructor
        $controllerInstance = new $controllerClass($dbInstance);

        if (method_exists($controllerInstance, $methodName)) {
            call_user_func_array([$controllerInstance, $methodName], $params);
            return;
        }
    }
        }

        // Si llega aquí, controlador o método no válidos → cargar ErrorController
        require_once __DIR__ . '/../controllers/ErrorController.php';
        $errorController = new \Controllers\ErrorController();
        $errorController->notFound();

    }
}
