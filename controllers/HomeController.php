<?php

namespace Controllers;

use Models\Banner;
use Models\Producto; // Asumiendo que tienes un modelo Producto

require_once __DIR__ .
    '/BaseController.php';

class HomeController extends BaseController
{
    public function index()
    {
        // Obtener banners principales
        $banners = Banner::obtenerActivosPorTipo('principal');
        $banners_secundarios_izquierda = Banner::obtenerActivosPorTipo('secundario_izquierda');
        $banners_secundarios_derecha = Banner::obtenerActivosPorTipo('secundario_derecha');

        // Obtener productos destacados
        // Asegúrate de que tu modelo Producto tenga un método 'obtenerDestacados'
        $productos_destacados = []; // Valor por defecto
        if (class_exists('Models\Producto') && method_exists('Models\Producto', 'obtenerDestacados')) {
            $productoModel = new Producto();
            $productos_destacados = $productoModel->obtenerDestacados();
        }

        $this->render('home/index', [
            'banners' => $banners,
            'banners_secundarios_izquierda' => $banners_secundarios_izquierda,
            'banners_secundarios_derecha' => $banners_secundarios_derecha,
            'productos_destacados' => $productos_destacados,
        ]);
    }
}
