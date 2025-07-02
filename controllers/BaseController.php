<?php
namespace Controllers;

class BaseController {
    public function render($view, $data = []) {
        extract($data);
        include "../views/{$view}.php";
    }
}
