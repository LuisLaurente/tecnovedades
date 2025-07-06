<?php

namespace Controllers;

use Models\Producto;
use Models\VarianteProducto;
use Models\Categoria;

class ProductoController
{
    public function index()
    {
        $productoModel = new Producto();
        $productos = $productoModel->obtenerTodos();

        // ✅ Agregar las categorías asociadas a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        }

        require_once __DIR__ . '/../views/producto/index.php';
    }

    public function crear()
    {
        $categorias = Categoria::obtenerTodas();
        require_once __DIR__ . '/../views/producto/crear.php';
    }

    public function guardar()
    {
        $db = \Core\Database::getInstance()->getConnection();

        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $visible = isset($_POST['visible']) ? 1 : 0;

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

        // ✅ Insertar relación producto-categoría
        if ($producto_id && !empty($_POST['categorias'])) {
            $sqlCat = "INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)";
            $stmtCat = $db->prepare($sqlCat);
            foreach ($_POST['categorias'] as $id_categoria) {
                $stmtCat->execute([$producto_id, $id_categoria]);
            }
        }

        // Insertar variantes si existen
        if ($producto_id && isset($_POST['variantes'])) {
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

        // Obtener categorías ya asociadas
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_categoria FROM producto_categoria WHERE id_producto = ?");
        $stmt->execute([$id]);
        $categoriasAsignadas = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_categoria');

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

            $db = \Core\Database::getInstance()->getConnection();

            // ✅ Actualizar categorías: eliminar todas y volver a insertar
            $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$id]);

            if (!empty($_POST['categorias'])) {
                $stmt = $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)");
                foreach ($_POST['categorias'] as $id_categoria) {
                    $stmt->execute([$id, $id_categoria]);
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
