<?php

namespace Models;

use Core\Database;
use PDO;

class Pedido
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($cliente_id, $monto_total, $estado = 'pendiente')
    {
        $stmt = $this->db->prepare("INSERT INTO pedidos (cliente_id, monto_total, estado) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $monto_total, $estado]);
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
        $sql = "SELECT * FROM pedidos";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
