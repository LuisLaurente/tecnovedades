<?php

namespace Controllers;

use Models\Producto;
use Models\Etiqueta;
use Models\Banner;

class HomeController
{
    // controllers/HomeController.php
    public function index()
    {
        $productoModel = new Producto();

        // --- Leer y validar filtros
        $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
        $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
        $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
        $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $etiquetasSeleccionadas = isset($_GET['etiquetas']) ? (array) $_GET['etiquetas'] : [];
        $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
        $orden = $_GET['orden'] ?? '';

        // --- Paginación: 6 filas x 4 columnas = 24 productos por página
        $productosPorPagina = 24;
        $paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0
            ? (int) $_GET['pagina'] : 1;
        $offset = ($paginaActual - 1) * $productosPorPagina;

        // --- Obtener productos paginados y total
        $productos = $productoModel->obtenerFiltrados(
            $minPrice,
            $maxPrice,
            $categoriaId,
            $etiquetasSeleccionadas,
            $soloDisponibles,
            $orden,
            true,
            $productosPorPagina,
            $offset
        );

        $totalFiltrados = $productoModel->contarFiltrados(
            $minPrice,
            $maxPrice,
            $categoriaId,
            $etiquetasSeleccionadas,
            true
        );

        $totalPaginas = (int) max(1, ceil($totalFiltrados / $productosPorPagina));

        // --- Enriquecer productos (categorías + imagen principal) - opcionalmente optimizable
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
            $imagen = \Models\ImagenProducto::obtenerPrimeraPorProducto($producto['id']);
            $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']);
            $producto['imagen'] = $imagen['nombre_imagen'] ?? 'placeholder.png';
        }
        unset($producto);

        // --- Otros recursos para la vista
        $categoriasDisponibles = Producto::obtenerCategoriasConProductos(true);
        $banners = Banner::obtenerActivos();
        $etiquetaModel = new Etiqueta();
        $todasEtiquetas = $etiquetaModel->obtenerTodas();

        // --- Query base (para construir links sin pagina/ajax)
        $currentQuery = $_GET;
        unset($currentQuery['pagina'], $currentQuery['ajax']);

        // --- Detectar AJAX
        $isAjax = !empty($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isAjax) {
            // Renderizar parciales a HTML y devolver JSON
            ob_start();
            // _products_grid.php debe usar $productos
            require __DIR__ . '/../views/home/_products_grid.php';
            $productsHtml = ob_get_clean();

            ob_start();
            // _pagination.php debe usar $paginaActual, $totalPaginas, $currentQuery
            require __DIR__ . '/../views/home/_pagination.php';
            $paginationHtml = ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'products_html' => $productsHtml,
                'pagination_html' => $paginationHtml,
                'page' => $paginaActual,
                'total_pages' => $totalPaginas
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Vista completa (no-AJAX)
        require_once __DIR__ . '/../views/home/index.php';
    }
}
