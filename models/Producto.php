<?php

namespace Models;

use Core\Database;
use PDO;

class Producto
{
    private $db;

    public function __construct()
    {
        // Conectamos a la base de datos usando la clase Database
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos()
    {
        // Consulta SQL para obtener todos los productos visibles
        $sql = "SELECT * FROM productos WHERE visible = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function obtenerPorId($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function actualizar($id, $nombre, $descripcion, $precio, $stock, $visible)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, visible = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $stock, $visible, $id]);
    }
    public static function eliminar($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE productos SET visible = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function obtenerCategoriasPorProducto($productoId)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
        SELECT c.nombre
        FROM categorias c
        INNER JOIN producto_categoria pc ON pc.id_categoria = c.id
        WHERE pc.id_producto = ?
    ");
        $stmt->execute([$productoId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre');
    }
    //etiqueta
    public function obtenerEtiquetasPorProducto($id_producto)
    {
        $stmt = $this->db->prepare("SELECT etiqueta_id FROM producto_etiqueta WHERE producto_id = ?");
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve array con IDs

    }

    public function obtenerFiltrados($min = null, $max = null)
    {
        // Sanitizar parámetros usando Validator
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        // Consulta base con campos necesarios y optimizada
        $sql = "SELECT id, nombre, descripcion, precio, stock, visible, created_at 
                FROM productos 
                WHERE visible = 1";
        
        $params = [];
        $conditions = [];

        // Aplicar filtro de precio mínimo
        if (!is_null($min) && $min >= 0) {
            $conditions[] = "precio >= :min_price";
            $params[':min_price'] = $min;
        }

        // Aplicar filtro de precio máximo
        if (!is_null($max) && $max >= 0) {
            $conditions[] = "precio <= :max_price";
            $params[':max_price'] = $max;
        }

        // Agregar condiciones a la consulta
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        // Ordenar por precio para mejor experiencia de usuario
        $sql .= " ORDER BY precio ASC, nombre ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $productos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Formatear precios para consistencia
            foreach ($productos as &$producto) {
                $producto['precio'] = number_format((float)$producto['precio'], 2, '.', '');
            }
            
            return $productos;
            
        } catch (\PDOException $e) {
            // En caso de error, log y devolver array vacío
            error_log("Error en obtenerFiltrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de precios para mejorar el filtrado
     */
    public function obtenerEstadisticasPrecios()
    {
        $sql = "SELECT 
                    MIN(precio) as precio_minimo,
                    MAX(precio) as precio_maximo,
                    AVG(precio) as precio_promedio,
                    COUNT(*) as total_productos
                FROM productos 
                WHERE visible = 1 AND precio > 0";
        
        try {
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'precio_minimo' => round((float)$resultado['precio_minimo'], 2),
                'precio_maximo' => round((float)$resultado['precio_maximo'], 2),
                'precio_promedio' => round((float)$resultado['precio_promedio'], 2),
                'total_productos' => (int)$resultado['total_productos']
            ];
            
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstadisticasPrecios: " . $e->getMessage());
            return [
                'precio_minimo' => 0,
                'precio_maximo' => 0,
                'precio_promedio' => 0,
                'total_productos' => 0
            ];
        }
    }

    /**
     * Contar productos que coinciden con los filtros
     */
    public function contarFiltrados($min = null, $max = null)
    {
        // Sanitizar parámetros usando Validator
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        $sql = "SELECT COUNT(*) as total FROM productos WHERE visible = 1";
        $params = [];
        $conditions = [];

        if (!is_null($min) && $min >= 0) {
            $conditions[] = "precio >= :min_price";
            $params[':min_price'] = $min;
        }

        if (!is_null($max) && $max >= 0) {
            $conditions[] = "precio <= :max_price";
            $params[':max_price'] = $max;
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return (int)$resultado['total'];
            
        } catch (\PDOException $e) {
            error_log("Error en contarFiltrados: " . $e->getMessage());
            return 0;
        }
    }
}
