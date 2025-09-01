<?php

namespace Models;

use Core\Database;
use PDO;

class DetallePedido {
    private $db;

    public function __construct() {
        $this->db = \Core\Database::getConexion();
    }

    public function crear($pedido_id, $producto_id, $cantidad, $precio_unitario, $variante_id = null) {
        $stmt = $this->db->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, variante_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$pedido_id, $producto_id, $variante_id, $cantidad, $precio_unitario]);
    }

    public function obtenerPorPedido($pedido_id) {
        // Método mejorado que incluye el nombre del producto
        $sql = "SELECT 
                    dp.*,
                    p.nombre as producto_nombre,
                    p.descripcion as producto_descripcion,
                    vp.talla as variante_talla,
                    vp.color as variante_color
                FROM detalle_pedido dp
                LEFT JOIN productos p ON dp.producto_id = p.id
                LEFT JOIN variantes_producto vp ON dp.variante_id = vp.id
                WHERE dp.pedido_id = ?
                ORDER BY dp.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pedido_id]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug temporal
        error_log("=== DEBUG obtenerPorPedido ===");
        error_log("Pedido ID: " . $pedido_id);
        error_log("Cantidad resultados: " . count($resultado));
        if (!empty($resultado)) {
            error_log("Primer resultado: " . json_encode($resultado[0]));
        }
        
        return $resultado;
    }

    public function obtenerPorPedidoConProductos($pedido_id) {
        $sql = "SELECT 
                    dp.*,
                    p.nombre as producto_nombre,
                    p.descripcion as producto_descripcion,
                    vp.talla as variante_talla,
                    vp.color as variante_color
                FROM detalle_pedido dp
                LEFT JOIN productos p ON dp.producto_id = p.id
                LEFT JOIN variantes_producto vp ON dp.variante_id = vp.id
                WHERE dp.pedido_id = ?
                ORDER BY dp.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pedido_id]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        return $resultado;
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
