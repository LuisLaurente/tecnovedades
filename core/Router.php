<?php
namespace Core;

class Router
{
    public function handleRequest($url)
    {
        $segments = explode('/', $url);

        $controllerName = ucfirst($segments[0] ?? 'home') . 'Controller';
        $methodName = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        $controllerClass = 'Controllers\\' . $controllerName;
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

           /* echo "<pre>";
            echo "Ruta solicitada: $url\n";
            echo "Archivo controlador: $controllerFile\n";
            echo "Clase: $controllerClass\n";
            echo "Método: $methodName\n";
            echo "</pre>";*/

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();

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
