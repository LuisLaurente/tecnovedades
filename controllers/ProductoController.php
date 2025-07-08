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
        $productos = $productoModel->obtenerTodos();


        // Agregar las categorías asociadas a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        }
        unset($producto);

        require_once __DIR__ . '/../views/producto/index.php';
    }

    public function crear()
    {
        // Obtener todas las categorías (para el select de categorías)
        $categorias = \Models\Categoria::obtenerTodas();

        // Obtener todas las etiquetas (para los checkboxes de etiquetas)
        $etiquetaModel = new \Models\Etiqueta();
        $etiquetas = $etiquetaModel->obtenerTodas();

        // Incluir la vista y pasarle los datos
        require_once __DIR__ . '/../views/producto/crear.php';
    }

    public function guardar()
    {
        $db = \Core\Database::getInstance()->getConnection();
        $errores = [];

        // 1. Recogemos los datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $visible = isset($_POST['visible']) ? 1 : 0;

        // 2. Validaciones básicas
        if (!\Core\Helpers\Validator::isRequired($nombre)) {
            $errores[] = "El nombre del producto es obligatorio.";
        }

        if (!\Core\Helpers\Validator::isNumeric($precio)) {
            $errores[] = "El precio debe ser un valor numérico.";
        }

        if (!\Core\Helpers\Validator::isNumeric($stock)) {
            $errores[] = "El stock debe ser un valor numérico.";
        }

        // (Opcional) Validación de imágenes
        if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] === 0) {
            $tipo = mime_content_type($_FILES['imagenes']['tmp_name'][0]);
            if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'])) {
                $errores[] = "La imagen debe ser JPG, PNG o WEBP.";
            }
        }

        // 3. Si hay errores, los mostramos
        if (!empty($errores)) {
            $categorias = \Models\Categoria::obtenerTodas();
            $etiquetaModel = new \Models\Etiqueta();
            $etiquetas = $etiquetaModel->obtenerTodas();
            require __DIR__ . '/../views/producto/crear.php';
            return;
        }

        // 4. Insertamos el producto
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, visible) 
            VALUES (:nombre, :descripcion, :precio, :stock, :visible)";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':visible', $visible);
        $stmt->execute();

        $producto_id = $db->lastInsertId();

        // 📦 5. Subimos imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $rutaDestino = __DIR__ . '/../public/uploads/';
            if (!is_dir($rutaDestino)) mkdir($rutaDestino, 0777, true);

            foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                $nombreOriginal = $_FILES['imagenes']['name'][$index];
                $nombreFinal = uniqid() . '_' . basename($nombreOriginal);
                $rutaFinal = $rutaDestino . $nombreFinal;

                if (move_uploaded_file($tmpName, $rutaFinal)) {
                    \Models\ImagenProducto::guardar($producto_id, $nombreFinal);
                }
            }
        }

        // 📌 6. Relación producto-categoría
        if (!empty($_POST['categorias'])) {
            $sqlCat = "INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)";
            $stmtCat = $db->prepare($sqlCat);
            foreach ($_POST['categorias'] as $id_categoria) {
                $stmtCat->execute([$producto_id, $id_categoria]);
            }
        }

        // 📌 7. Relación producto-etiqueta
        $etiquetas = $_POST['etiquetas'] ?? [];
        foreach ($etiquetas as $etiqueta_id) {
            $stmt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$producto_id, $etiqueta_id]);
        }

        // 📌 8. Variantes del producto
        if (isset($_POST['variantes'])) {
            $variantes = $_POST['variantes'];
            $tallas = $variantes['talla'] ?? [];
            $colores = $variantes['color'] ?? [];
            $stocks = $variantes['stock'] ?? [];

            $sqlVariante = "INSERT INTO variantes_producto (producto_id, talla, color, stock) 
                        VALUES (:producto_id, :talla, :color, :stock)";
            $stmtVariante = $db->prepare($sqlVariante);

            for ($i = 0; $i < count($tallas); $i++) {
                $stmtVariante->execute([
                    ':producto_id' => $producto_id,
                    ':talla'       => trim($tallas[$i]),
                    ':color'       => trim($colores[$i]),
                    ':stock'       => (int)$stocks[$i]
                ]);
            }
        }

        // 📌 9. Redirigimos al listado
        header("Location: /producto");
        exit;
    }



    public function editar($id)
    {
        $producto = Producto::obtenerPorId($id);
        if (!$producto) {
            echo "Producto no encontrado.";
            return;
        }
        $variantes = VarianteProducto::obtenerPorProductoId($id);
        $categorias = Categoria::obtenerTodas();
        //etiquetas
        $etiquetaModel = new Etiqueta;
        $etiquetas = $etiquetaModel->obtenerTodas();

        // Obtener categorías ya asociadas
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_categoria FROM producto_categoria WHERE id_producto = ?");
        $stmt->execute([$id]);
        $categoriasAsignadas = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_categoria');


        $etiquetasAsignadas = $etiquetaModel->obtenerEtiquetasPorProducto($id);


        $imagenes = \Models\ImagenProducto::obtenerPorProducto($id);
        // Incluyo la vista del formulario de edición
        require __DIR__ . '/../views/producto/editar.php';
    }

    public function actualizar()
    {
        //  Obtengo la conexión a la base de datos
        $db = \Core\Database::getInstance()->getConnection();

        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $visible = isset($_POST['visible']) ? (int) $_POST['visible'] : 1;

        if ($id) {
            // Actualizar producto
            Producto::actualizar($id, $nombre, $descripcion, $precio, $stock, $visible);

            // Actualizar categorías
            $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$id]);

            if (!empty($_POST['categorias'])) {
                $stmt = $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)");
                foreach ($_POST['categorias'] as $id_categoria) {
                    $stmt->execute([$id, $id_categoria]);
                }
            }

            // Actualizar etiquetas
            $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?")->execute([$id]);

            if (!empty($_POST['etiquetas'])) {
                foreach ($_POST['etiquetas'] as $etiqueta_id) {
                    $stmt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
                    $stmt->execute([$id, $etiqueta_id]);
                }
            }
        }

        header('Location: /producto');
        exit;
    }

    public function eliminar($id)
    {
        Producto::eliminar($id);
        header('Location: /producto');
        exit;
    }
}
