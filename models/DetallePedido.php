<?php

namespace Models;

use Core\Database;
use PDO;

class DetallePedido {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($pedido_id, $producto_id, $cantidad, $precio_unitario, $variante_id = null) {
        $stmt = $this->db->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, variante_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$pedido_id, $producto_id, $variante_id, $cantidad, $precio_unitario]);
    }

    public function obtenerPorPedido($pedido_id) {
        $stmt = $this->db->prepare("SELECT * FROM detalle_pedido WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarPorPedido($pedido_id) {
        $stmt = $this->db->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
        return $stmt->execute([$pedido_id]);
    }
    public function existePedido($pedido_id)
    {
        $sql = "SELECT COUNT(*) FROM detalle_pedido WHERE pedido_id = :pedido_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pedido_id', $pedido_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
