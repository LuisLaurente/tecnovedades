<?php
// controllers/HomeController.php

namespace Controllers;

use Models\Producto;
use Models\Banner;
// Añade los otros modelos que necesites
use Models\Categoria;
use Models\Etiqueta;

class HomeController
{
    /**
     * ==================================================================
     *  MÉTODO INDEX: PARA EL NUEVO HOME VISUAL
     * ==================================================================
     * Este método ahora solo se encarga de cargar los datos necesarios
     * para la página de inicio principal (el diseño del carrusel).
     */
    public function index()
    {
        // 1. Instanciar los modelos necesarios.
        $productoModel = new Producto();
        $bannerModel = new Banner();

        // 2. Obtener SOLAMENTE los productos destacados usando el nuevo método.
        $productosDestacados = $productoModel->obtenerDestacados(12); // Trae hasta 12 productos destacados.

        // 3. Obtener los banners activos.
        $banners = $bannerModel->obtenerActivos();

        // 4. Preparar los datos para la vista.
        //    La clave es 'productos' para que la parcial _products_grid.php funcione sin cambios.
        $datos = [
            'productos' => $productosDestacados,
            'banners'   => $banners,
        ];

        $productos = $productosDestacados; 

        // 5. Cargar la vista del home (la que tiene el carrusel).
        require_once __DIR__ . '/../views/home/index.php';
    }

    /**
     * ==================================================================
     *  NUEVO MÉTODO BUSQUEDA: PARA LA TIENDA CON FILTROS
     * ==================================================================
     * Aquí hemos movido TODA tu lógica original de filtros y paginación.
     * Esta será la página que muestre la grilla completa de productos.
     */
    public function busqueda()
    {
        $productoModel = new Producto();

        // --- (LÓGICA ORIGINAL MOVIDA AQUÍ) Leer y validar filtros ---
        $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
        $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
        $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
        $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $etiquetasSeleccionadas = isset($_GET['etiquetas']) ? (array) $_GET['etiquetas'] : [];
        $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
        $orden = $_GET['orden'] ?? '';

        // --- Paginación ---
        $productosPorPagina = 24;
        $paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0
            ? (int) $_GET['pagina'] : 1;
        $offset = ($paginaActual - 1) * $productosPorPagina;

        // --- Obtener productos paginados y total ---
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

        // --- Enriquecer productos (imágenes, etc.) ---
        foreach ($productos as &$producto) {
            $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']);
            $producto = $productoModel->prepararProductoParaVista($producto);
        }
        unset($producto);

        // --- Otros recursos para la vista de búsqueda ---
        $categoriasDisponibles = Categoria::obtenerPadres();
        $etiquetaModel = new Etiqueta();
        $todasEtiquetas = $etiquetaModel->obtenerTodas();

        // --- Query base para links ---
        $currentQuery = $_GET;
        unset($currentQuery['pagina'], $currentQuery['ajax']);

        // --- Lógica AJAX (se mantiene igual) ---
        $isAjax = !empty($_GET['ajax']);
        if ($isAjax) {
            ob_start();
            require __DIR__ . '/../views/home/_products_grid.php';
            $productsHtml = ob_get_clean();

            ob_start();
            require __DIR__ . '/../views/home/_pagination.php';
            $paginationHtml = ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'products_html' => $productsHtml,
                'pagination_html' => $paginationHtml,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // --- Cargar la vista completa de búsqueda (no-AJAX) ---
        // Esta vista (busqueda.php) contendrá la grilla, los filtros, etc.
        require_once __DIR__ . '/../views/home/busqueda.php';
    }
}
