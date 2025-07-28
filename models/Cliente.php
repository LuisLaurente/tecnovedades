<?php

namespace Models;

use Core\Database;
use PDO;

class Cliente {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($nombre_completo, $direccion, $telefono = null, $correo = null) {
        $stmt = $this->db->prepare("INSERT INTO clientes (nombre_completo, direccion, telefono, correo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre_completo, $direccion, $telefono, $correo]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM clientes";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $nombre_completo, $direccion, $telefono = null, $correo = null) {
        $stmt = $this->db->prepare("UPDATE clientes SET nombre_completo = ?, direccion = ?, telefono = ?, correo = ? WHERE id = ?");
        return $stmt->execute([$nombre_completo, $direccion, $telefono, $correo, $id]);
    }

    public function eliminar($id) {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
