<?php
namespace Controllers;

class ErrorController extends BaseController {
    public function notFound() {
        echo "<h1>Error 404 - Página no encontrada</h1>";
    }
}
