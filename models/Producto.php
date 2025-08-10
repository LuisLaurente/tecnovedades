<?php

namespace Models;

use Core\Database;
use PDO;

class Producto
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos($etiquetas = [], $soloDisponibles = false, $orden = '')
    {
        $sql = "SELECT DISTINCT p.* FROM productos p";
        $params = [];

        if (!empty($etiquetas)) {
            $etiquetaPlaceholders = [];
            foreach ($etiquetas as $i => $etiquetaId) {
                $key = ":etiqueta_$i";
                $etiquetaPlaceholders[] = $key;
                $params[$key] = $etiquetaId;
            }
            $sql .= " JOIN producto_etiqueta pe ON p.id = pe.producto_id
                      WHERE pe.etiqueta_id IN (" . implode(',', $etiquetaPlaceholders) . ")";
        } else {
            $sql .= " WHERE 1=1";
        }

        $sql .= " AND p.visible = 1";

        if ($soloDisponibles) {
            $sql .= " AND p.stock > 0";
        }

        $ordenesValidos = [
            'precio_asc'   => 'p.precio ASC',
            'precio_desc'  => 'p.precio DESC',
            'nombre_asc'   => 'p.nombre ASC',
            'nombre_desc'  => 'p.nombre DESC',
            'fecha_desc'   => 'p.created_at DESC'
        ];

        if (array_key_exists($orden, $ordenesValidos)) {
            $sql .= " ORDER BY " . $ordenesValidos[$orden];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerFiltrados($min = null, $max = null, $categoriaId = null, $etiquetas = [], $soloDisponibles = false, $orden = '')
    {
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        $sql = "SELECT DISTINCT p.* FROM productos p";
        $joins = [];
        $conditions = ["p.visible = 1"];
        $params = [];

        if (!empty($etiquetas)) {
            $joins[] = "LEFT JOIN producto_etiqueta pe ON p.id = pe.producto_id";
            $etiquetaPlaceholders = [];
            foreach ($etiquetas as $i => $etiquetaId) {
                $key = ":etiqueta_$i";
                $etiquetaPlaceholders[] = $key;
                $params[$key] = $etiquetaId;
            }
            $conditions[] = "pe.etiqueta_id IN (" . implode(',', $etiquetaPlaceholders) . ")";
        }

        if (!is_null($categoriaId) && $categoriaId > 0) {
            $joins[] = "INNER JOIN producto_categoria pc ON p.id = pc.id_producto";
            $categoriaIds = [$categoriaId];
            $stmt = $this->db->prepare("SELECT id FROM categorias WHERE id_padre = :categoria_padre");
            $stmt->execute([':categoria_padre' => $categoriaId]);
            $subcats = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subcats)) {
                $categoriaIds = array_merge($categoriaIds, $subcats);
            }

            $catPlaceholders = [];
            foreach ($categoriaIds as $i => $catId) {
                $key = ":cat_$i";
                $catPlaceholders[] = $key;
                $params[$key] = $catId;
            }
            $conditions[] = "pc.id_categoria IN (" . implode(",", $catPlaceholders) . ")";
        }

        if (!is_null($min) && $min >= 0) {
            $conditions[] = "p.precio >= :min_price";
            $params[':min_price'] = $min;
        }

        if (!is_null($max) && $max >= 0) {
            $conditions[] = "p.precio <= :max_price";
            $params[':max_price'] = $max;
        }

        if ($soloDisponibles) {
            $conditions[] = "p.stock > 0";
        }

        if (!empty($joins)) {
            $sql .= " " . implode(" ", $joins);
        }

        $sql .= " WHERE " . implode(" AND ", $conditions);

        // Aplicar ordenamiento
        $ordenesValidos = [
            'precio_asc'   => 'p.precio ASC',
            'precio_desc'  => 'p.precio DESC',
            'nombre_asc'   => 'p.nombre ASC',
            'nombre_desc'  => 'p.nombre DESC',
            'fecha_desc'   => 'p.created_at DESC'
        ];

        if (array_key_exists($orden, $ordenesValidos)) {
            $sql .= " ORDER BY " . $ordenesValidos[$orden];
        } else {
            $sql .= " ORDER BY p.precio ASC, p.nombre ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as &$producto) {
            $producto['precio'] = number_format((float)$producto['precio'], 2, '.', '');
        }

        return $productos;
    }

    public function contarFiltrados($min = null, $max = null, $categoriaId = null, $etiquetas = null)
    {
        $min = \Core\Helpers\Validator::sanitizarPrecio($min);
        $max = \Core\Helpers\Validator::sanitizarPrecio($max);

        $sql = "SELECT COUNT(DISTINCT p.id) as total FROM productos p";
        $joins = [];
        $conditions = ["p.visible = 1"];
        $params = [];

        if (!is_null($categoriaId) && $categoriaId > 0) {
            $joins[] = "INNER JOIN producto_categoria pc ON p.id = pc.id_producto";
            $categoriaIds = [$categoriaId];

            $stmt = $this->db->prepare("SELECT id FROM categorias WHERE id_padre = :categoria_padre");
            $stmt->execute([':categoria_padre' => $categoriaId]);
            $subcats = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($subcats)) {
                $categoriaIds = array_merge($categoriaIds, $subcats);
            }

            $catPlaceholders = [];
            foreach ($categoriaIds as $i => $catId) {
                $key = ":cat_$i";
                $catPlaceholders[] = $key;
                $params[$key] = $catId;
            }

            $conditions[] = "pc.id_categoria IN (" . implode(",", $catPlaceholders) . ")";
        }

        // Filtro por etiquetas
        if (!empty($etiquetas) && is_array($etiquetas)) {
            $etiquetas = array_filter($etiquetas); // Eliminar valores vacíos
            if (!empty($etiquetas)) {
                $joins[] = "INNER JOIN producto_etiqueta pe ON p.id = pe.producto_id";
                $etPlaceholders = [];
                foreach ($etiquetas as $i => $etId) {
                    $key = ":et_$i";
                    $etPlaceholders[] = $key;
                    $params[$key] = (int)$etId;
                }
                $conditions[] = "pe.etiqueta_id IN (" . implode(",", $etPlaceholders) . ")";
            }
        }

        if (!is_null($min)) {
            $conditions[] = "p.precio >= :min_price";
            $params[':min_price'] = $min;
        }

        if (!is_null($max)) {
            $conditions[] = "p.precio <= :max_price";
            $params[':max_price'] = $max;
        }

        if (!empty($joins)) {
            $sql .= " " . implode(" ", $joins);
        }

        $sql .= " WHERE " . implode(" AND ", $conditions);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function obtenerEstadisticasPrecios()
    {
        $sql = "SELECT 
                    MIN(precio) as precio_minimo,
                    MAX(precio) as precio_maximo,
                    AVG(precio) as precio_promedio,
                    COUNT(*) as total_productos
                FROM productos 
                WHERE visible = 1 AND precio > 0";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'precio_minimo' => round((float)$res['precio_minimo'], 2),
            'precio_maximo' => round((float)$res['precio_maximo'], 2),
            'precio_promedio' => round((float)$res['precio_promedio'], 2),
            'total_productos' => (int)$res['total_productos']
        ];
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

    public function obtenerEtiquetasPorProducto($productoId)
    {
        $stmt = $this->db->prepare("SELECT etiqueta_id FROM producto_etiqueta WHERE producto_id = ?");
        $stmt->execute([$productoId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function obtenerCategoriasConProductos()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT DISTINCT c.id, c.nombre, c.id_padre, COUNT(pc.id_producto) as productos_directos
                FROM categorias c
                INNER JOIN producto_categoria pc ON c.id = pc.id_categoria
                INNER JOIN productos p ON pc.id_producto = p.id
                WHERE p.visible = 1
                GROUP BY c.id, c.nombre, c.id_padre
                ORDER BY c.nombre ASC";

        $stmt = $db->query($sql);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categorias as &$categoria) {
            $stmtSub = $db->prepare("
                SELECT COUNT(pc.id_producto) as productos_subcategoria
                FROM categorias sub
                INNER JOIN producto_categoria pc ON sub.id = pc.id_categoria
                INNER JOIN productos p ON pc.id_producto = p.id
                WHERE sub.id_padre = ? AND p.visible = 1
            ");
            $stmtSub->execute([$categoria['id']]);
            $resultSub = $stmtSub->fetch(PDO::FETCH_ASSOC);
            $categoria['total_productos'] = $categoria['productos_directos'] + ($resultSub['productos_subcategoria'] ?? 0);
        }

        return $categorias;
    }
    public function obtenerVisibles()
    {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM productos WHERE visible = 1");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function buscarPorNombre($q)
    {
        $db = \Core\Database::getInstance()->getConnection();

        $sql = "
        SELECT p.id, p.nombre, p.descripcion, p.precio,
               (SELECT ip.nombre_imagen 
                FROM imagenes_producto ip 
                WHERE ip.producto_id = p.id 
                LIMIT 1) AS imagen
        FROM productos p
        WHERE p.nombre LIKE :q OR p.descripcion LIKE :q
        LIMIT 20
    ";

        $stmt = $db->prepare($sql);
        $like = "%$q%";
        $stmt->execute([':q' => $like]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
