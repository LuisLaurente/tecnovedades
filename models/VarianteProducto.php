<?php

namespace Models;

use Core\Database;
use PDO;

class VarianteProducto
{
    private $db;

    public function __construct()
    {
        // Me conecto a la base de datos
        $this->db = Database::getInstance()->getConnection();
    }

    // Este método me permite registrar una nueva variante
    public function crear($producto_id, $talla, $color, $stock)
    {
        $sql = "INSERT INTO variantes_producto (producto_id, talla, color, stock) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$producto_id, $talla, $color, $stock]);
    }

    // Este método me permite obtener todas las variantes de un producto específico
    public function obtenerPorProducto($producto_id)
    {
        $sql = "SELECT * FROM variantes_producto WHERE producto_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Este método me permite eliminar todas las variantes de un producto (opcionalmente útil en edición)
    public function eliminarPorProducto($producto_id)
    {
        $sql = "DELETE FROM variantes_producto WHERE producto_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$producto_id]);
    }
    // Este método me permite obtener variantes por ID de producto
    public static function obtenerPorProductoId($producto_id)
    {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM variantes_producto WHERE producto_id = ?");
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Este método me permite actualizar una variante existente
    public static function actualizar($id, $talla, $color, $stock)
    {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE variantes_producto SET talla = ?, color = ?, stock = ? WHERE id = ?");
        $stmt->execute([$talla, $color, $stock, $id]);
    }
    // Este método me permite eliminar una variante por su ID
    public static function eliminar($id)
    {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM variantes_producto WHERE id = ?");
        $stmt->execute([$id]);
    }
}
