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

    public function obtenerFiltrados($min = null, $max = null, $categoriaId = null)
    {
        // Sanitizar parámetros usando Validator
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        // Consulta base con campos necesarios y optimizada
        $sql = "SELECT DISTINCT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.visible, p.created_at 
                FROM productos p";

        // Si hay filtro por categoría, hacer JOIN con la tabla de relación
        $categoriaIds = [];
        if (!is_null($categoriaId) && $categoriaId > 0) {
            $sql .= " INNER JOIN producto_categoria pc ON p.id = pc.id_producto";

            // Obtener IDs de subcategorías
            $categoriaIds = [$categoriaId];
            $db = $this->db;
            $stmt = $db->prepare("SELECT id FROM categorias WHERE id_padre = ?");
            $stmt->execute([$categoriaId]);
            $subcats = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($subcats)) {
                $categoriaIds = array_merge($categoriaIds, $subcats);
            }
        }

        $sql .= " WHERE p.visible = 1";

        $params = [];
        $conditions = [];

        // Aplicar filtro de precio mínimo
        if (!is_null($min) && $min >= 0) {
            $conditions[] = "p.precio >= :min_price";
            $params[':min_price'] = $min;
        }

        // Aplicar filtro de precio máximo
        if (!is_null($max) && $max >= 0) {
            $conditions[] = "p.precio <= :max_price";
            $params[':max_price'] = $max;
        }

        // Aplicar filtro por categoría o subcategoría
        if (!empty($categoriaIds)) {
            $inClause = [];
            foreach ($categoriaIds as $i => $catId) {
                $key = ":cat_$i";
                $inClause[] = $key;
                $params[$key] = $catId;
            }
            $conditions[] = "pc.id_categoria IN (" . implode(",", $inClause) . ")";
        }

        // Agregar condiciones a la consulta
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        // Ordenar por precio para mejor experiencia de usuario
        $sql .= " ORDER BY p.precio ASC, p.nombre ASC";

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
    public function contarFiltrados($min = null, $max = null, $categoriaId = null)
    {
        // Sanitizar parámetros usando Validator
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM productos p";
        
        // Si hay filtro por categoría, hacer JOIN con la tabla de relación
        $categoriaIds = [];
        if (!is_null($categoriaId) && $categoriaId > 0) {
            $sql .= " INNER JOIN producto_categoria pc ON p.id = pc.id_producto";

            // Obtener IDs de subcategorías
            $categoriaIds = [$categoriaId];
            $db = $this->db;
            $stmt = $db->prepare("SELECT id FROM categorias WHERE id_padre = ?");
            $stmt->execute([$categoriaId]);
            $subcats = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($subcats)) {
                $categoriaIds = array_merge($categoriaIds, $subcats);
            }
        }
        
        $sql .= " WHERE p.visible = 1";
        
        $params = [];
        $conditions = [];

        if (!is_null($min) && $min >= 0) {
            $conditions[] = "p.precio >= :min_price";
            $params[':min_price'] = $min;
        }

        if (!is_null($max) && $max >= 0) {
            $conditions[] = "p.precio <= :max_price";
            $params[':max_price'] = $max;
        }

        // Aplicar filtro por categoría o subcategoría
        if (!empty($categoriaIds)) {
            $inClause = [];
            foreach ($categoriaIds as $i => $catId) {
                $key = ":cat_$i";
                $inClause[] = $key;
                $params[$key] = $catId;
            }
            $conditions[] = "pc.id_categoria IN (" . implode(",", $inClause) . ")";
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

    /**
     * Obtener categorías con productos para el filtro
     */
    public static function obtenerCategoriasConProductos()
    {
        $db = Database::getInstance()->getConnection();
        
        // Obtener todas las categorías con su conteo directo de productos
        $sql = "SELECT DISTINCT c.id, c.nombre, c.id_padre, COUNT(pc.id_producto) as productos_directos
                FROM categorias c
                INNER JOIN producto_categoria pc ON c.id = pc.id_categoria
                INNER JOIN productos p ON pc.id_producto = p.id
                WHERE p.visible = 1
                GROUP BY c.id, c.nombre, c.id_padre
                ORDER BY c.nombre ASC";
        
        try {
            $stmt = $db->query($sql);
            $categorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Para cada categoría, calcular el total incluyendo subcategorías
            foreach ($categorias as &$categoria) {
                $totalProductos = $categoria['productos_directos'];
                
                // Buscar subcategorías y sumar sus productos
                $sqlSubcategorias = "SELECT COUNT(pc.id_producto) as productos_subcategoria
                                    FROM categorias sub
                                    INNER JOIN producto_categoria pc ON sub.id = pc.id_categoria
                                    INNER JOIN productos p ON pc.id_producto = p.id
                                    WHERE sub.id_padre = ? AND p.visible = 1";
                
                $stmtSub = $db->prepare($sqlSubcategorias);
                $stmtSub->execute([$categoria['id']]);
                $resultSub = $stmtSub->fetch(\PDO::FETCH_ASSOC);
                
                if ($resultSub) {
                    $totalProductos += $resultSub['productos_subcategoria'];
                }
                
                $categoria['total_productos'] = $totalProductos;
            }
            
            return $categorias;
            
        } catch (\PDOException $e) {
            error_log("Error en obtenerCategoriasConProductos: " . $e->getMessage());
            return [];
        }
    }
}
