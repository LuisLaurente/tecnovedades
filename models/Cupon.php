<?php
namespace Models;

use Core\Database;
use PDO;

class Cupon
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPorCodigo($codigo) {
        $sql = "SELECT * FROM cupones 
                WHERE codigo = :codigo AND activo=1 
                AND CURDATE() BETWEEN fecha_inicio AND fecha_fin";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarUso($cupon_id, $usuario_id, $pedido_id) {
        $sql = "INSERT INTO cupon_usado (cupon_id, usuario_id, pedido_id)
                VALUES (:cupon_id, :usuario_id, :pedido_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cupon_id', $cupon_id);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':pedido_id', $pedido_id);
        return $stmt->execute();
    }

    public function contarUsos($cupon_id, $usuario_id = null) {
        $sql = "SELECT COUNT(*) FROM cupon_usado WHERE cupon_id = :cupon_id";
        if ($usuario_id) {
            $sql .= " AND usuario_id = :usuario_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cupon_id', $cupon_id);
        if ($usuario_id) $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
