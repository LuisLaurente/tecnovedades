<?php
namespace Controllers;

class BaseController {
    public function render($vista, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $vista . '.php';
    }
}
