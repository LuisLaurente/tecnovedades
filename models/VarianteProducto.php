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
}
