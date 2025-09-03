<?php

namespace Controllers;
use Core\Database;
use Models\Producto;
use Models\VarianteProducto;
use Models\ImagenProducto;
use Models\Categoria;
use Models\Etiqueta;
use PDO;
use PDOException;
class ProductoController extends BaseController
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

        // Asociar categorías e imágenes a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
            $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']); // 🔑 Aquí añadimos todas las imágenes
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
        // 🔹 Variables SEO para listado
        $metaTitle = 'Catálogo de productos | Tienda Tecnovedades';
        $metaDescription = 'Explora nuestro catálogo con la mejor tecnología y novedades a precios increíbles.';
        $metaImage = url('images/catalogo-share.png');
        $canonical = url('producto');

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
        $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
        $precio_tachado = $_POST['precio_tachado'] ?? null;
        $stock = $_POST['stock'] ?? '';
        $visible = isset($_POST['visible']) ? 1 : 0;
        $destacado = isset($_POST['destacado']) ? 1 : 0; // ⭐ Nuevo campo

        // flags de visibilidad
        $precio_tachado_visible = isset($_POST['precio_tachado_visible']) ? 1 : 0;
        $porcentaje_visible = isset($_POST['porcentaje_visible']) ? 1 : 0;

        // Validaciones básicas
        if (!\Core\Helpers\Validator::isRequired($nombre)) $errores[] = "El nombre del producto es obligatorio.";
        if (!\Core\Helpers\Validator::isNumeric($precio)) $errores[] = "El precio debe ser un valor numérico.";
        if (!\Core\Helpers\Validator::isNumeric($stock)) $errores[] = "El stock debe ser un valor numérico.";

        // Validar precio_tachado y recalcular porcentaje
        if ($precio_tachado !== null && $precio_tachado !== '') {
            if (!is_numeric($precio_tachado) || (float)$precio_tachado <= $precio) {
                // Si es inválido, no lo usamos y desactivamos la visibilidad
                $precio_tachado = null;
                $precio_tachado_visible = 0;
                $porcentaje_descuento = 0;
                $porcentaje_visible = 0;
            } else {
                $precio_tachado = (float)$precio_tachado;
                $porcentaje_descuento = round((($precio_tachado - $precio) / $precio_tachado) * 100);
            }
        } else {
            $precio_tachado = null;
            $porcentaje_descuento = 0;
            $precio_tachado_visible = 0;
            $porcentaje_visible = 0;
        }

        // Validar imagen principal
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

        /// Antes de la inserción, captura los campos:
        $especificaciones = trim($_POST['especificaciones'] ?? '');
        $productos_relacionados_input = $_POST['productos_relacionados'] ?? []; // array de ids
        // Normalizar a array de ints
        $productos_relacionados_input = array_map('intval', (array)$productos_relacionados_input);
        $productos_relacionados_json = !empty($productos_relacionados_input) ? json_encode($productos_relacionados_input) : null;

        // INSERT actualizando la query:
        $stmt = $db->prepare("INSERT INTO productos 
        (nombre, descripcion, precio, precio_tachado, porcentaje_descuento, 
     precio_tachado_visible, porcentaje_visible, stock, visible, destacado,
     especificaciones, productos_relacionados) 
        VALUES (:nombre, :descripcion, :precio, :precio_tachado, :porcentaje_descuento, 
        :precio_tachado_visible, :porcentaje_visible, :stock, :visible, :destacado,
        :especificaciones, :productos_relacionados)");

        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':precio_tachado' => $precio_tachado,
            ':porcentaje_descuento' => $porcentaje_descuento,
            ':precio_tachado_visible' => $precio_tachado_visible,
            ':porcentaje_visible' => $porcentaje_visible,
            ':stock' => $stock,
            ':visible' => $visible,
            ':destacado' => $destacado,
            ':especificaciones' => $especificaciones ?: null,
            ':productos_relacionados' => $productos_relacionados_json
        ]);

        $producto_id = $db->lastInsertId();

        // ... resto (imágenes, categorías, etiquetas, variantes) sin cambios ...
        header("Location: " . url("producto/index"));
        exit;
    }

    public function editar($id)
    {
        $producto = Producto::obtenerPorId($id);
        if (!$producto) {
            echo "Producto no encontrado.";
            return;
        }

        // 🔹 Aseguramos que el campo 'destacado' exista aunque no venga en DB
        if (!isset($producto['destacado'])) {
            $producto['destacado'] = 0;
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

        $allProducts = (new Producto())->obtenerVisibles();

        require __DIR__ . '/../views/producto/editar.php';
    }

    public function actualizar()
    {
        $db = \Core\Database::getInstance()->getConnection();

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = isset($_POST['precio']) ? (float) $_POST['precio'] : 0;
        $precio_tachado = $_POST['precio_tachado'] ?? null;
        $stock = isset($_POST['stock']) ? (int) $_POST['stock'] : 0;
        $visible = isset($_POST['visible']) ? 1 : 0;

        // ✅ Nuevo: checkbox destacado
        $destacado = isset($_POST['destacado']) ? 1 : 0;

        // flags de visibilidad (checkboxes)
        $precio_tachado_visible = isset($_POST['precio_tachado_visible']) ? 1 : 0;
        $porcentaje_visible = isset($_POST['porcentaje_visible']) ? 1 : 0;

        // Validar precio_tachado
        if ($precio_tachado !== null && $precio_tachado !== '') {
            if (!is_numeric($precio_tachado) || (float)$precio_tachado <= $precio) {
                $precio_tachado = null;
                $precio_tachado_visible = 0;
                $porcentaje_descuento = 0;
                $porcentaje_visible = 0;
            } else {
                $precio_tachado = (float)$precio_tachado;
                $porcentaje_descuento = round((($precio_tachado - $precio) / $precio_tachado) * 100);
            }
        } else {
            $precio_tachado = null;
            $porcentaje_descuento = 0;
            $precio_tachado_visible = 0;
            $porcentaje_visible = 0;
        }

        // ✅ Actualizar producto con campo "destacado" agregado
        // Recoger inputs
        $especificaciones = trim($_POST['especificaciones'] ?? '');
        $productos_relacionados_input = $_POST['productos_relacionados'] ?? [];
        $productos_relacionados_input = array_map('intval', (array)$productos_relacionados_input);
        $productos_relacionados_json = !empty($productos_relacionados_input) ? json_encode($productos_relacionados_input) : null;

        Producto::actualizar(
            $id,
            $nombre,
            $descripcion,
            $precio,
            $precio_tachado,
            $porcentaje_descuento,
            $precio_tachado_visible,
            $porcentaje_visible,
            $stock,
            $visible,
            $destacado,
            $especificaciones,
            $productos_relacionados_json
        );


        // ------- categorías -------
        $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$id]);
        foreach ($_POST['categorias'] ?? [] as $cat_id) {
            $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)")->execute([$id, $cat_id]);
        }

        // ------- etiquetas -------
        $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?")->execute([$id]);
        foreach ($_POST['etiquetas'] ?? [] as $etiqueta_id) {
            $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)")->execute([$id, $etiqueta_id]);
        }

        // (👉 todo tu bloque de imágenes se mantiene igual, no lo repito para no duplicar)

        // Mensaje de éxito si no hay errores específicos
        if (empty($_SESSION['flash_error'])) {
            $_SESSION['flash_success'] = 'Producto actualizado correctamente.';
        } else {
            if (is_array($_SESSION['flash_error'])) {
                $_SESSION['flash_error'] = implode(' ', $_SESSION['flash_error']);
            }
        }

        header("Location: " . url("producto/editar/$id"));
        exit;
    }

    public function eliminar($id)
    {
        Producto::eliminar($id);
        header('Location: ' . url('carrito/ver'));
        exit;
    }
    public function listarPublico()
    {
        $productoModel = new Producto();

        // ✅ Solo obtener productos visibles
        $productos = $productoModel->obtenerVisibles();

        // Asociar categorías a cada producto (opcional)
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        }
        unset($producto);

        // Mostrar la vista pública (home)
        require_once __DIR__ . '/../public/home.php';
    }
    public function autocomplete()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        header('Content-Type: application/json');

        if ($q === '') {
            echo json_encode([]);
            exit;
        }

        $productoModel = new \Models\Producto();
        $resultados = $productoModel->buscarPorNombre($q);

        // Puedes mapear/limpiar campos si quieres reducir tamaño
        echo json_encode($resultados);
        exit;
    }
    public function ver($id)
{
    $productoModel = new Producto();

    if (!is_numeric($id)) {
        header('Location: ' . url('producto'));
        exit;
    }

    $producto = $productoModel->obtenerPorId((int)$id);

    if (!$producto) {
        header('Location: ' . url('producto'));
        exit;
    }

    $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
    $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']);

    // --- Breadcrumb ---
    try {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_categoria FROM producto_categoria WHERE id_producto = ?");
        $stmt->execute([(int)$producto['id']]);
        $catIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $bestChain = [];
        foreach ($catIds as $cid) {
            $chain = \Models\Categoria::obtenerAncestros((int)$cid);
            if (count($chain) > count($bestChain)) {
                $bestChain = $chain;
            }
        }

        $breadcrumb = !empty($bestChain)
            ? array_column($bestChain, 'nombre')
            : ['Inicio', 'Productos'];

        $breadcrumb[] = $producto['nombre'];
    } catch (\Throwable $e) {
        error_log('ProductoController::ver - error construyendo breadcrumb: ' . $e->getMessage());
        $breadcrumb = ['Inicio', 'Productos', $producto['nombre']];
    }

    // 🔹 SEO dinámico
    $meta_title = $producto['nombre'] . ' | Tienda Tecnovedades';
    $meta_description = substr(strip_tags($producto['descripcion']), 0, 160);
    $meta_image = !empty($producto['imagenes'][0]['nombre_imagen'])
        ? url('uploads/' . $producto['imagenes'][0]['nombre_imagen'])
        : url('images/default-share.png');
    $canonical = url('producto/ver/' . $producto['id']);

    // ------------------- Relacionados -------------------
    $relatedProducts = [];
    $ids = $producto['productos_relacionados'] ?? [];

    if (!empty($ids)) {
        // evita incluirse a sí mismo por si acaso
        $ids = array_values(array_diff(array_map('intval', $ids), [(int)$producto['id']]));

        if (!empty($ids)) {
            $relatedProducts = $productoModel->obtenerPorIds($ids);

            // Carga primera imagen y expón clave 'imagen' que espera la vista
            foreach ($relatedProducts as &$rp) {
                $rp['imagenes'] = \Models\ImagenProducto::obtenerPorProducto((int)$rp['id']);
                $rp['imagen'] = !empty($rp['imagenes'][0]['nombre_imagen'])
                    ? $rp['imagenes'][0]['nombre_imagen']
                    : null;
            }
            unset($rp);
        }
    }

    // ------------------- Reseñas -------------------
    try {
        $stmt = $db->prepare("
            SELECT r.*, u.nombre AS usuario_nombre
            FROM product_reviews r
            JOIN usuarios u ON r.user_id = u.id
            WHERE r.producto_id = :id AND r.estado = 'aprobado'
            ORDER BY r.created_at DESC
        ");
        $stmt->bindParam(':id', $producto['id'], \PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        error_log('ProductoController::ver - error cargando reseñas: ' . $e->getMessage());
        $reviews = [];
    }

    // ------------------- Vista -------------------
    require_once __DIR__ . '/../views/producto/descripcion.php';
}


    public function busqueda()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($q)) {
            // Si no hay búsqueda, redirigimos al home
            header('Location: ' . url('home/index'));
            exit;
        }

        $productoModel = new \Models\Producto();
        $resultados = $productoModel->buscarPorNombre($q);

        // Definir $termino para que exista en la vista
        $termino = $q;

        // 🔹 Variables SEO para resultados de búsqueda
        $metaTitle = 'Resultados para "' . htmlspecialchars($termino) . '" | Tienda Tecnovedades';
        $metaDescription = 'Encuentra los mejores productos relacionados con "' . htmlspecialchars($termino) . '".';
        $metaImage = url('images/busqueda-share.png');
        $canonical = url('producto/busqueda?q=' . urlencode($termino));

        // Renderizamos la vista de búsqueda
        include __DIR__ . '/../views/home/busqueda.php';
    }
    public function guardarComentario()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = \Core\Database::getConexion();

        $producto_id = $_POST['producto_id'] ?? null;
        $orden_id    = $_POST['orden_id'] ?? null;
        $user_id     = $_POST['user_id'] ?? null; // lo recibes del formulario o de la sesión
        $puntuacion  = $_POST['puntuacion'] ?? null;
        $titulo      = $_POST['titulo'] ?? null;
        $texto       = $_POST['texto'] ?? null;

        if ($producto_id && $orden_id && $user_id && $puntuacion) {
            $stmt = $db->prepare("
                INSERT INTO product_reviews 
                (producto_id, user_id, orden_id, puntuacion, titulo, texto, created_at)
                VALUES (:producto_id, :user_id, :orden_id, :puntuacion, :titulo, :texto, NOW())
            ");

            $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':orden_id', $orden_id, PDO::PARAM_INT);
            $stmt->bindParam(':puntuacion', $puntuacion, PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                header("Location: " . url('usuario/pedidos')); // regresa a pedidos
                 $_SESSION['flash'] = "✅ Comentario guardado con éxito";
                exit;
            } else {
                $_SESSION['flash'] = "❌ Error al guardar el comentario";
            }
        } else {
            $_SESSION['flash'] = "⚠️ Faltan datos para guardar el comentario";
        }
        // Redirigir siempre a pedidos
        header("Location: " . url('usuario/pedidos'));
        exit;
    }
}





}
