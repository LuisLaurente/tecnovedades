<?php

namespace Controllers;

use Models\Producto;

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
        // Primero obtengo la conexión a la base de datos
        $db = \Core\Database::getInstance()->getConnection();

        // Luego, obtengo los datos enviados desde el formulario
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $visible = isset($_POST['visible']) ? 1 : 0;

        // Esta consulta me sirve para insertar el producto en la base de datos
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, visible) 
                VALUES (:nombre, :descripcion, :precio, :stock, :visible)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':visible', $visible);

        // Ejecuto la consulta
        $stmt->execute();

        // Después de guardar, redirijo al listado de productos
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

        // Redirijo al listado de productos
        header('Location: /producto');
        exit;
    }

}
