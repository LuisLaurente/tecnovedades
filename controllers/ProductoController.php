<?php

namespace Controllers;

use Etiqueta as GlobalEtiqueta;
use Models\Producto;
use Models\VarianteProducto;
use Models\Etiqueta;

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

        //  Obtengo el ID del producto recién creado
        $producto_id = $db->lastInsertId();

        //etiquetas

        $etiquetas = $_POST['etiquetas'] ?? [];

        foreach ($etiquetas as $etiqueta_id) {
        $stmt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
        $stmt->execute([$producto_id, $etiqueta_id]);
        }

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
        $variantes = VarianteProducto::obtenerPorProductoId($id);

        

        //etiquetas
        $etiquetaModel = new Etiqueta;
        $etiquetas = $etiquetaModel->obtenerTodas();
        $etiquetasAsignadas = $etiquetaModel->obtenerEtiquetasPorProducto($id);


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
            // Actualizar datos del producto
        Producto::actualizar($id, $nombre, $descripcion, $precio, $stock, $visible);
    
        // ETIQUETAS
        // 1. Eliminar etiquetas actuales
        $stmt = $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?");
        $stmt->execute([$id]);

        // Insertar nuevas etiquetas seleccionadas
        $etiquetas = $_POST['etiquetas'] ?? [];
        foreach ($etiquetas as $etiqueta_id) {
            $stmt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$id, $etiqueta_id]);
        }

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
