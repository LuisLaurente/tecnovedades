<?php

namespace Controllers;

use Models\Producto;
use Models\Etiqueta;
use Models\Banner;

class HomeController
{
public function index()
{
    $productoModel = new Producto();

    // Filtros
    $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
    $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
    $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
    $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $etiquetasSeleccionadas = $_GET['etiquetas'] ?? [];
    $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
    $orden = $_GET['orden'] ?? '';

    // 📌 Paginación
    $productosPorPagina = 8; // o 7, como prefieras
    $paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 
        ? (int)$_GET['pagina'] 
        : 1;
    $offset = ($paginaActual - 1) * $productosPorPagina;

    // Datos para filtros
    $estadisticasPrecios = $productoModel->obtenerEstadisticasPrecios(true);
    $categoriasDisponibles = Producto::obtenerCategoriasConProductos(true);

    // ✅ Obtener productos limitados por página
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

    $totalPaginas = ceil($totalFiltrados / $productosPorPagina);

    // Asociar imagen principal
    foreach ($productos as &$producto) {
        $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        $imagen = \Models\ImagenProducto::obtenerPrimeraPorProducto($producto['id']);
        $producto['imagen'] = $imagen['nombre_imagen'] ?? 'placeholder.png';
    }
    unset($producto);

    // Etiquetas
    $etiquetaModel = new Etiqueta();
    $todasEtiquetas = $etiquetaModel->obtenerTodas();

    // Banners
    $banners = Banner::obtenerActivos();

    require_once __DIR__ . '/../views/home/index.php';
}

}
