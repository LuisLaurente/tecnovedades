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

    public function obtenerTodos($etiquetas = [], $soloDisponibles = false, $orden = '')
    {
        $sql = "SELECT DISTINCT p.* FROM productos p";
        $params = [];

        if (!empty($etiquetas)) {
            $sql .= " 
            JOIN producto_etiqueta pe ON p.id = pe.producto_id
            WHERE pe.etiqueta_id IN (" . implode(',', array_fill(0, count($etiquetas), '?')) . ")";
            $params = array_merge($params, $etiquetas);
        } else {
            $sql .= " WHERE 1=1";
        }

        $sql .= " AND p.visible = 1";

        if ($soloDisponibles) {
            $sql .= " AND p.stock > 0";
        }
        // Validar ordenamiento
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
    /*public function obtenerFiltradosPorEtiquetas($etiquetas)
    {
        $sql = "SELECT DISTINCT p.* 
            FROM productos p
            INNER JOIN producto_etiqueta pe ON p.id = pe.producto_id 
            WHERE pe.etiqueta_id IN (" . implode(',', array_fill(0, count($etiquetas), '?')) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($etiquetas);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }*/
    public function obtenerFiltrados($etiquetas = [], $soloDisponibles = false)
    {
        $sql = "SELECT DISTINCT p.* 
            FROM productos p 
            LEFT JOIN producto_etiqueta pe ON p.id = pe.producto_id 
            WHERE 1";

        $parametros = [];

        if (!empty($etiquetas)) {
            $placeholders = implode(',', array_fill(0, count($etiquetas), '?'));
            $sql .= " AND pe.etiqueta_id IN ($placeholders)";
            $parametros = array_merge($parametros, $etiquetas);
        }

        if ($soloDisponibles) {
            $sql .= " AND p.stock > 0";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
