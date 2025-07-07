<?php

namespace Controllers;

use Models\Producto;
use Models\VarianteProducto;
use Models\ImagenProducto;

class ProductoController
{
    public function index()
    {
        // Instanciamos el modelo de productos
        $productoModel = new Producto();

        // Obtenemos la lista de productos desde la base de datos
        $productos = $productoModel->obtenerTodos();

        // Cargamos la vista y le pasamos los productos
        require_once __DIR__ . '/../views/producto/index.php';
    }
    public function crear()
    {
        // Esta línea sirve para mostrar la vista que contiene el formulariop
        require_once __DIR__ . '/../views/producto/crear.php';
    }
    public function guardar()
    {
        //  Obtengo la conexión a la base de datos
        $db = \Core\Database::getInstance()->getConnection();

        //  Obtengo los datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $visible = isset($_POST['visible']) ? 1 : 0;

        //  Inserto el producto en la tabla productos
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, visible) 
                VALUES (:nombre, :descripcion, :precio, :stock, :visible)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':visible', $visible);
        $stmt->execute();

        $idProducto = $db->lastInsertId(); // ID del producto insertado

        // Procesar imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $rutaDestino = __DIR__ . '/../public/uploads/';

            if (!is_dir($rutaDestino)) {
                mkdir($rutaDestino, 0777, true); // Crear carpeta si no existe
            }

            foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                $nombreOriginal = $_FILES['imagenes']['name'][$index];
                $nombreFinal = uniqid() . '_' . basename($nombreOriginal);
                $rutaFinal = $rutaDestino . $nombreFinal;

                if (move_uploaded_file($tmpName, $rutaFinal)) {
                    ImagenProducto::guardar($idProducto, $nombreFinal);
                }
            }
        }

        //  Obtengo el ID del producto recién creado
        $producto_id = $db->lastInsertId();

        //  Verifico si se enviaron variantes
        if ($producto_id && isset($_POST['variantes'])) {
            $variantes = $_POST['variantes'];

            $tallas = $variantes['talla'] ?? [];
            $colores = $variantes['color'] ?? [];
            $stocks = $variantes['stock'] ?? [];

            //  Recorro todas las variantes y las inserto en la tabla variantes_producto
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

        //  Redirijo al listado de productos después de guardar
        header("Location: /producto/index");
        exit;
    }

    public function editar($id)
    {
        // Obtengo el producto desde el modelo por su ID
        $producto = Producto::obtenerPorId($id);

        if (!$producto) {
            echo "Producto no encontrado.";
            return;
        }

        // 🧩 Obtener variantes de este producto
        $variantes = \Models\VarianteProducto::obtenerPorProductoId($id);

        $imagenes = \Models\ImagenProducto::obtenerPorProducto($id);
        

        // Incluyo la vista del formulario de edición
        require __DIR__ . '/../views/producto/editar.php';
    }
    public function actualizar()
    {
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $visible = isset($_POST['visible']) ? (int) $_POST['visible'] : 1;

        if ($id) {
            Producto::actualizar($id, $nombre, $descripcion, $precio, $stock, $visible);
        }

        header('Location: /producto');
        exit;
    }
    public function eliminar($id)
    {
        // Llamo al método eliminar del modelo Producto
        Producto::eliminar($id);

        // Redirijo al listado de productoss
        header('Location: /producto');
        exit;
    }

}
