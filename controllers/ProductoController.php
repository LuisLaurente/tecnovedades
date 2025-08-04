<?php

namespace Controllers;

use Models\Producto;
use Models\VarianteProducto;
use Models\ImagenProducto;
use Models\Categoria;
use Models\Etiqueta;

class ProductoController
{
    public function index()
    {
        $productoModel = new Producto();

        // Filtros comunes
        $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
        $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
        $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
        $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;

        // Filtros adicionales por etiquetas y stock
        $etiquetasSeleccionadas = $_GET['etiquetas'] ?? [];
        $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
        $orden = $_GET['orden'] ?? '';

        // Obtener datos para filtros y visualización
        $estadisticasPrecios = $productoModel->obtenerEstadisticasPrecios();
        $categoriasDisponibles = Producto::obtenerCategoriasConProductos();
        $productos = $productoModel->obtenerFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas, $soloDisponibles, $orden);
        $totalFiltrados = $productoModel->contarFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas);

        // Asociar categorías a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        }
        unset($producto);

        // Obtener todas las etiquetas para el formulario
        $etiquetaModel = new Etiqueta();
        $todasEtiquetas = $etiquetaModel->obtenerTodas();

        // Si es petición AJAX, devolver JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
            
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

        // Mostrar vista
        require_once __DIR__ . '/../views/producto/index.php';
    }

    public function crear()
    {
        $categorias = Categoria::obtenerTodas();
        $etiquetas = (new Etiqueta())->obtenerTodas();
        require_once __DIR__ . '/../views/producto/crear.php';
    }

    public function guardar()
    {
        $db = \Core\Database::getInstance()->getConnection();
        $errores = [];

        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!\Core\Helpers\Validator::isRequired($nombre)) $errores[] = "El nombre del producto es obligatorio.";
        if (!\Core\Helpers\Validator::isNumeric($precio)) $errores[] = "El precio debe ser un valor numérico.";
        if (!\Core\Helpers\Validator::isNumeric($stock)) $errores[] = "El stock debe ser un valor numérico.";

        if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] === 0) {
            $tipo = mime_content_type($_FILES['imagenes']['tmp_name'][0]);
            if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'])) {
                $errores[] = "La imagen debe ser JPG, PNG o WEBP.";
            }
        }

        if (!empty($errores)) {
            $categorias = Categoria::obtenerTodas();
            $etiquetas = (new Etiqueta())->obtenerTodas();
            require __DIR__ . '/../views/producto/crear.php';
            return;
        }

        $stmt = $db->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, visible) 
                              VALUES (:nombre, :descripcion, :precio, :stock, :visible)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':visible' => $visible
        ]);
        $producto_id = $db->lastInsertId();

        // Imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $rutaDestino = __DIR__ . '/../public/uploads/';
            if (!is_dir($rutaDestino)) mkdir($rutaDestino, 0777, true);

            foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmpName) {
                $nombreFinal = uniqid() . '_' . basename($_FILES['imagenes']['name'][$i]);
                if (move_uploaded_file($tmpName, $rutaDestino . $nombreFinal)) {
                    ImagenProducto::guardar($producto_id, $nombreFinal);
                }
            }
        }

        // Categorías
        if (!empty($_POST['categorias'])) {
            $stmt = $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)");
            foreach ($_POST['categorias'] as $cat_id) {
                $stmt->execute([$producto_id, $cat_id]);
            }
        }

        // Etiquetas
        foreach ($_POST['etiquetas'] ?? [] as $etiqueta_id) {
            $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)")
               ->execute([$producto_id, $etiqueta_id]);
        }

        // Variantes
        $variantes = $_POST['variantes'] ?? [];
        if (!empty($variantes)) {
            $stmt = $db->prepare("INSERT INTO variantes_producto (producto_id, talla, color, stock) 
                                  VALUES (:producto_id, :talla, :color, :stock)");
            foreach ($variantes['talla'] ?? [] as $i => $talla) {
                $stmt->execute([
                    ':producto_id' => $producto_id,
                    ':talla' => trim($talla),
                    ':color' => trim($variantes['color'][$i]),
                    ':stock' => (int) $variantes['stock'][$i]
                ]);
            }
        }

        header("Location: " . url("producto/index"));
        exit;
    }

    public function editar($id)
    {
        $producto = Producto::obtenerPorId($id);
        if (!$producto) {
            echo "Producto no encontrado."; return;
        }

        $variantes = VarianteProducto::obtenerPorProductoId($id);
        $categorias = Categoria::obtenerTodas();
        $etiquetaModel = new Etiqueta();
        $etiquetas = $etiquetaModel->obtenerTodas();
        $etiquetasAsignadas = $etiquetaModel->obtenerEtiquetasPorProducto($id);

        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_categoria FROM producto_categoria WHERE id_producto = ?");
        $stmt->execute([$id]);
        $categoriasAsignadas = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_categoria');

        $imagenes = ImagenProducto::obtenerPorProducto($id);
        require __DIR__ . '/../views/producto/editar.php';
    }

    public function actualizar()
    {
        $db = \Core\Database::getInstance()->getConnection();
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $visible = isset($_POST['visible']) ? 1 : 0;

        Producto::actualizar($id, $nombre, $descripcion, $precio, $stock, $visible);

        // Categorías
        $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$id]);
        foreach ($_POST['categorias'] ?? [] as $cat_id) {
            $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)")
               ->execute([$id, $cat_id]);
        }

        // Etiquetas
        $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?")->execute([$id]);
        foreach ($_POST['etiquetas'] ?? [] as $etiqueta_id) {
            $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)")
               ->execute([$id, $etiqueta_id]);
        }

        header("Location: /producto/editar/$id");
        exit;
    }

    public function eliminar($id)
    {
        Producto::eliminar($id);
        header('Location: ' . url('carrito/ver'));
        exit;
    }
}
