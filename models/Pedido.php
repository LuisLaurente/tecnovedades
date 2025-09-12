<?php

namespace Models;

use Core\Database;
use PDO;

class Pedido
{
    private $db;

    public function __construct()
    {
        $this->db = \Core\Database::getConexion();
    }

    public function crear($usuario_id, $monto_total, $estado = 'pendiente', $pedido_data = null)
    {
        if ($pedido_data) {
            $stmt = $this->db->prepare("
            INSERT INTO pedidos (
                cliente_id, 
                monto_total, 
                estado, 
                cupon_id, 
                cupon_codigo, 
                descuento_cupon, 
                subtotal, 
                descuento_promocion,
                promociones_aplicadas,  -- ← ¡NUEVO CAMPO AQUÍ!
                costo_envio,
                facturacion_tipo_documento,
                facturacion_numero_documento,
                facturacion_nombre,
                facturacion_direccion,
                facturacion_email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)  -- ← Un parámetro más aquí
        ");
            $stmt->execute([
                $usuario_id,
                $monto_total,
                $estado,
                $pedido_data['cupon_id'] ?? null,
                $pedido_data['cupon_codigo'] ?? null,
                $pedido_data['descuento_cupon'] ?? 0.00,
                $pedido_data['subtotal'] ?? 0.00,
                $pedido_data['descuento_promocion'] ?? 0.00,
                $pedido_data['promociones_aplicadas'] ?? null,  // ← ¡NUEVO CAMPO AQUÍ!
                $pedido_data['costo_envio'] ?? 0.00,
                $pedido_data['facturacion_tipo_documento'] ?? null,
                $pedido_data['facturacion_numero_documento'] ?? null,
                $pedido_data['facturacion_nombre'] ?? null,
                $pedido_data['facturacion_direccion'] ?? null,
                $pedido_data['facturacion_email'] ?? null
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO pedidos (cliente_id, monto_total, estado) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $monto_total, $estado]);
        }
        return $this->db->lastInsertId();
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM pedidos ORDER BY creado_en DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosConDirecciones()
    {
        try {
            $sql = "
                SELECT p.*, 
                       COALESCE(
                           pd.direccion_temporal,
                           CONCAT(
                               d.direccion,
                               CASE 
                                   WHEN d.distrito IS NOT NULL OR d.provincia IS NOT NULL OR d.departamento IS NOT NULL 
                                   THEN CONCAT(', ', 
                                       COALESCE(CONCAT(d.distrito, CASE WHEN d.provincia IS NOT NULL OR d.departamento IS NOT NULL THEN ', ' ELSE '' END), ''),
                                       COALESCE(CONCAT(d.provincia, CASE WHEN d.departamento IS NOT NULL THEN ', ' ELSE '' END), ''),
                                       COALESCE(d.departamento, '')
                                   )
                                   ELSE ''
                               END,
                               CASE WHEN d.referencia IS NOT NULL THEN CONCAT(' - ', d.referencia) ELSE '' END
                           )
                       ) as direccion_envio
                FROM pedidos p
                LEFT JOIN pedido_direcciones pd ON p.id = pd.pedido_id
                LEFT JOIN direcciones d ON pd.direccion_id = d.id
                ORDER BY p.creado_en DESC
            ";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return $this->obtenerTodos();
        }
    }

    public function actualizarEstado($id, $estado)
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->db->prepare("DELETE FROM pedidos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function actualizarObservacionesAdmin($id, $observacion)
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET observaciones_admin = ? WHERE id = ?");
        return $stmt->execute([$observacion, $id]);
    }
}
