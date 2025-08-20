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

        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as &$producto) {
            $producto = $this->prepararProductoParaVista($producto);
        }

        return $productos;
    }

    public function obtenerFiltrados(
        $minPrice = null,
        $maxPrice = null,
        $categoriaId = null,
        array $etiquetas = [],
        $soloDisponibles = false,
        $orden = '',
        $visibleOnly = true,
        $limit = null,
        $offset = null
    ) {
        $db = \Core\Database::getInstance()->getConnection();

        list($whereSql, $params) = $this->buildWhereClause($minPrice, $maxPrice, $categoriaId, $etiquetas, $visibleOnly);

        // disponibilidad por stock: detectamos columna de stock si existe
        if ($soloDisponibles) {
            $cols = $this->getTableColumns('productos');
            $stockCols = ['stock', 'cantidad', 'cantidad_stock', 'qty', 'cantidad_disponible', 'disponible'];
            $foundStockCol = null;
            foreach ($stockCols as $c) {
                if (in_array($c, $cols, true)) {
                    $foundStockCol = $c;
                    break;
                }
            }
            if ($foundStockCol) {
                // si ya hay WHERE, añadimos con AND
                $whereSql .= ($whereSql ? " AND " : " WHERE ") . "p.`{$foundStockCol}` > 0";
            }
        }

        // Orden
        $orderSql = " ORDER BY p.id DESC"; // por defecto
        switch ($orden) {
            case 'precio_asc':
                $orderSql = " ORDER BY p.precio ASC";
                break;
            case 'precio_desc':
                $orderSql = " ORDER BY p.precio DESC";
                break;
            case 'nombre_asc':
                $orderSql = " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $orderSql = " ORDER BY p.nombre DESC";
                break;
            default:
                // si existe columna destacado, puedes ordenar por ella primero
                $prodCols = $this->getTableColumns('productos');
                if (in_array('destacado', $prodCols, true)) {
                    $orderSql = " ORDER BY p.destacado DESC, p.id DESC";
                } else {
                    $orderSql = " ORDER BY p.id DESC";
                }
                break;
        }

        $sql = "SELECT p.* FROM productos p" . $whereSql . $orderSql;

        if (is_numeric($limit) && is_numeric($offset)) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            // binds params dinámicos
            foreach ($params as $k => $v) {
                // inferir tipo
                if (is_int($v)) $stmt->bindValue($k, $v, \PDO::PARAM_INT);
                else $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                if (is_int($v)) $stmt->bindValue($k, $v, \PDO::PARAM_INT);
                else $stmt->bindValue($k, $v);
            }
            $stmt->execute();
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve array con los nombres de columnas de la tabla indicada (o [] si no existe / error)
     */
    private function getTableColumns(string $table): array
    {
        try {
            $db = \Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
            $cols = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_column($cols, 'Field');
        } catch (\Throwable $e) {
            // tabla no existe o error -> devolvemos array vacío
            return [];
        }
    }


    public function contarFiltrados(
        $minPrice = null,
        $maxPrice = null,
        $categoriaId = null,
        array $etiquetas = [],
        $visibleOnly = true
    ) {
        $db = \Core\Database::getInstance()->getConnection();

        list($whereSql, $params) = $this->buildWhereClause($minPrice, $maxPrice, $categoriaId, $etiquetas, $visibleOnly);

        $sql = "SELECT COUNT(*) AS total FROM productos p" . $whereSql;
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            if (is_int($v)) $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            else $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
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

    public static function actualizar(
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
        $destacado
    ) {
        try {
            $db = Database::getInstance()->getConnection();

            $sql = "
            UPDATE productos
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                precio = :precio,
                precio_tachado = :precio_tachado,
                porcentaje_descuento = :porcentaje_descuento,
                precio_tachado_visible = :precio_tachado_visible,
                porcentaje_visible = :porcentaje_visible,
                stock = :stock,
                visible = :visible,
                destacado = :destacado
            WHERE id = :id
        ";

            $stmt = $db->prepare($sql);

            // binds
            $stmt->bindValue(':nombre', $nombre, \PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $descripcion, \PDO::PARAM_STR);

            // precio puede ser decimal: lo pasamos tal cual (PDO no tiene FLOAT const)
            $stmt->bindValue(':precio', $precio);

            // precio_tachado puede ser NULL
            if ($precio_tachado === null || $precio_tachado === '') {
                $stmt->bindValue(':precio_tachado', null, \PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':precio_tachado', $precio_tachado);
            }

            // porcentaje entero
            $stmt->bindValue(':porcentaje_descuento', (int)$porcentaje_descuento, \PDO::PARAM_INT);

            $stmt->bindValue(':precio_tachado_visible', (int)$precio_tachado_visible, \PDO::PARAM_INT);
            $stmt->bindValue(':porcentaje_visible', (int)$porcentaje_visible, \PDO::PARAM_INT);
            $stmt->bindValue(':stock', (int)$stock, \PDO::PARAM_INT);
            $stmt->bindValue(':visible', (int)$visible, \PDO::PARAM_INT);
            $stmt->bindValue(':destacado', (int)$destacado, \PDO::PARAM_INT);

            $stmt->bindValue(':id', (int)$id, \PDO::PARAM_INT);

            $ok = $stmt->execute();

            return (bool)$ok;
        } catch (\PDOException $e) {
            // Log para que puedas revisar el error exacto en los logs del servidor
            error_log("[Producto::actualizar] Error PDO: " . $e->getMessage() . " -- SQL: " . $e->getTraceAsString());
            return false;
        }
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
    public function buscarPorNombre(string $q): array
    {
        $db = Database::getInstance()->getConnection();

        $sql = "
        SELECT p.*,
               (SELECT ip.nombre_imagen
                FROM imagenes_producto ip
                WHERE ip.producto_id = p.id
                ORDER BY ip.id ASC
                LIMIT 1) AS imagen
        FROM productos p
        WHERE (p.nombre LIKE :q OR p.descripcion LIKE :q)
          AND p.visible = 1
        LIMIT 100
    ";

        $stmt = $db->prepare($sql);
        $like = '%' . $q . '%';
        $stmt->execute([':q' => $like]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Calcula el precio final basado en precio_tachado y porcentaje_descuento.
     * Si el precio final (precio) no coincide, puede usarse para validaciones o ajustes.
     * 
     * @param float|null $precioTachado
     * @param float|null $porcentajeDescuento
     * @return float|null Precio final calculado o null si no hay datos suficientes.
     */
    public function calcularPrecioFinal(?float $precioTachado, ?float $porcentajeDescuento): ?float
    {
        if ($precioTachado !== null && $porcentajeDescuento !== null) {
            $descuento = $precioTachado * ($porcentajeDescuento / 100);
            return round($precioTachado - $descuento, 2);
        }
        return null;
    }

    /**
     * Formatea un número como precio con 2 decimales y punto decimal.
     * 
     * @param mixed $precio
     * @return string
     */
    public function formatearPrecio($precio): string
    {
        return number_format((float)$precio, 2, '.', '');
    }

    /**
     * Obtiene el texto para el badge del porcentaje, por ejemplo "-40%".
     * Retorna cadena vacía si no hay descuento válido.
     * 
     * @param float|null $porcentajeDescuento
     * @return string
     */
    public function obtenerTextoBadge(?float $porcentajeDescuento): string
    {
        if ($porcentajeDescuento !== null && $porcentajeDescuento > 0) {
            return '-' . $this->formatearPrecio($porcentajeDescuento) . '%';
        }
        return '';
    }

    /**
     * Completa los datos de un producto con cálculo y formateo de precios y porcentaje.
     * Modifica el array $producto directamente.
     * 
     * @param array $producto Producto con claves 'precio', 'precio_tachado', 'porcentaje_descuento'
     * @return array Producto modificado con datos formateados y precio calculado.
     */
    public function prepararProductoParaVista(array $producto): array
    {
        $producto['precio'] = $this->formatearPrecio($producto['precio'] ?? 0);
        $producto['precio_tachado'] = isset($producto['precio_tachado']) ? $this->formatearPrecio($producto['precio_tachado']) : null;
        $producto['porcentaje_descuento'] = isset($producto['porcentaje_descuento']) ? (float)$producto['porcentaje_descuento'] : 0;

        // flags (aseguramos booleanos; default true si no existe para compatibilidad)
        $producto['precio_tachado_visible'] = isset($producto['precio_tachado_visible']) ? (bool)$producto['precio_tachado_visible'] : true;
        $producto['porcentaje_visible'] = isset($producto['porcentaje_visible']) ? (bool)$producto['porcentaje_visible'] : true;

        // Si no hay precio tachado o no es mayor, ocultamos
        if (empty($producto['precio_tachado']) || (float)$producto['precio_tachado'] <= (float)$producto['precio']) {
            $producto['precio_tachado'] = null;
            $producto['porcentaje_descuento'] = 0;
            $producto['precio_tachado_visible'] = false;
            $producto['porcentaje_visible'] = false;
        }

        // Texto para badge solo si porcentaje_visible = true y porcentaje > 0
        $producto['texto_badge'] = ($producto['porcentaje_visible'] && $producto['porcentaje_descuento'] > 0)
            ? '-' . $this->formatearPrecio($producto['porcentaje_descuento']) . '%'
            : '';

        return $producto;
    }

    private function buildWhereClause($minPrice, $maxPrice, $categoriaId, array $etiquetas, $visibleOnly)
    {
        $where = [];
        $params = [];

        if ($visibleOnly) {
            $where[] = "p.visible = 1";
        }

        if ($minPrice !== null && $minPrice !== '') {
            $where[] = "p.precio >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $where[] = "p.precio <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        // --- Filtrar por categoría (detectamos columnas reales en producto_categoria) ---
        if (!empty($categoriaId)) {
            $pcCols = $this->getTableColumns('producto_categoria');
            // posibles nombres
            $prodColCandidates = ['producto_id', 'id_producto', 'productoId', 'productoId']; // posibles variantes
            $catColCandidates  = ['categoria_id', 'id_categoria', 'categoriaId'];

            $prodCol = null;
            $catCol  = null;
            foreach ($prodColCandidates as $c) if (in_array($c, $pcCols, true)) {
                $prodCol = $c;
                break;
            }
            foreach ($catColCandidates as $c) if (in_array($c, $pcCols, true)) {
                $catCol = $c;
                break;
            }

            // Si no detectamos, probamos nombres por defecto que usa tu proyecto: id_producto/id_categoria
            if ($prodCol === null) $prodCol = in_array('id_producto', $pcCols, true) ? 'id_producto' : (in_array('producto_id', $pcCols, true) ? 'producto_id' : null);
            if ($catCol === null)  $catCol  = in_array('id_categoria', $pcCols, true) ? 'id_categoria' : (in_array('categoria_id', $pcCols, true) ? 'categoria_id' : null);

            if ($prodCol !== null && $catCol !== null) {
                // usamos columnas detectadas
                $where[] = "EXISTS (SELECT 1 FROM producto_categoria pc WHERE pc.`{$prodCol}` = p.id AND pc.`{$catCol}` = :categoriaId)";
                $params[':categoriaId'] = (int)$categoriaId;
            } else {
                // si no existe la tabla o no encontramos columnas, ignoramos el filtro para evitar fallo fatal
                // (podrías añadir logging aquí)
            }
        }

        // --- Filtrar por etiquetas (producto_etiqueta) ---
        if (!empty($etiquetas) && is_array($etiquetas)) {
            $peCols = $this->getTableColumns('producto_etiqueta');
            $prodColPeCandidates = ['producto_id', 'id_producto'];
            $etColPeCandidates   = ['etiqueta_id', 'id_etiqueta', 'etiquetaId'];

            $prodColPe = null;
            $etColPe = null;
            foreach ($prodColPeCandidates as $c) if (in_array($c, $peCols, true)) {
                $prodColPe = $c;
                break;
            }
            foreach ($etColPeCandidates as $c)  if (in_array($c, $peCols, true)) {
                $etColPe = $c;
                break;
            }

            if ($prodColPe === null) $prodColPe = in_array('producto_id', $peCols, true) ? 'producto_id' : (in_array('id_producto', $peCols, true) ? 'id_producto' : null);
            if ($etColPe === null)   $etColPe   = in_array('etiqueta_id', $peCols, true) ? 'etiqueta_id' : (in_array('id_etiqueta', $peCols, true) ? 'id_etiqueta' : null);

            if ($prodColPe !== null && $etColPe !== null) {
                $placeholders = [];
                foreach ($etiquetas as $idx => $et) {
                    $ph = ':et' . $idx;
                    $placeholders[] = $ph;
                    $params[$ph] = (int)$et;
                }
                if (!empty($placeholders)) {
                    $where[] = "EXISTS (SELECT 1 FROM producto_etiqueta pe WHERE pe.`{$prodColPe}` = p.id AND pe.`{$etColPe}` IN (" . implode(',', $placeholders) . "))";
                }
            } else {
                // si no detectamos, ignoramos filtro de etiquetas
            }
        }

        $whereSql = count($where) ? " WHERE " . implode(" AND ", $where) : "";

        return [$whereSql, $params];
    }
}
