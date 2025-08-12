<?php

namespace Controllers;

use Models\Producto;
use Models\Etiqueta;

class HomeController
{
    public function index()
    {
        $productoModel = new Producto();

        // ✅ Filtros comunes (mismos que en ProductoController)
        $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
        $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
        $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
        $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;

        // ✅ Filtros adicionales
        $etiquetasSeleccionadas = $_GET['etiquetas'] ?? [];
        $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
        $orden = $_GET['orden'] ?? '';

        // ✅ Obtener datos para filtros y visualización (solo productos visibles)
        $estadisticasPrecios = $productoModel->obtenerEstadisticasPrecios(true); // ← si tienes soporte para visibles
        $categoriasDisponibles = Producto::obtenerCategoriasConProductos(true); // ← igual aquí, si hay variante pública
        $productos = $productoModel->obtenerFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas, $soloDisponibles, $orden, true); 
        $totalFiltrados = $productoModel->contarFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas, true);

        // ✅ Asociar categorías a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
            $imagen = \Models\ImagenProducto::obtenerPrimeraPorProducto($producto['id']);
            $producto['imagen'] = $imagen['nombre_imagen'] ?? 'placeholder.png';
        }
        unset($producto);

        // ✅ Obtener etiquetas
        $etiquetaModel = new Etiqueta();
        $todasEtiquetas = $etiquetaModel->obtenerTodas();

        // ✅ Si es petición AJAX (filtros dinámicos en Home)
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => empty($validacionFiltros['errores']),
                'productos' => $productos,
                'total' => $totalFiltrados,
                'filtros' => [
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'categoria' => $categoriaId,
                    'etiquetas' => $etiquetasSeleccionadas,
                    'disponibles' => $soloDisponibles,
                    'orden' => $orden
                ],
                'errores' => $validacionFiltros['errores'] ?? []
            ]);
            exit;
        }

        // 🔹 Variables SEO para la portada
        $meta_title = "Tienda Tecnovedades - Productos tecnológicos y novedades";
        $meta_description = "Explora nuestra tienda online con lo último en tecnología, accesorios y novedades a precios increíbles.";
        $meta_image = url('images/default-share.png'); // Logo o imagen destacada
        $canonical = url('home/index');

        // ✅ Mostrar vista
        require_once __DIR__ . '/../views/home/index.php';
    }
}
