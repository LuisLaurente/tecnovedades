<?php
namespace Models;

use Core\Database;
use PDO;

class Reclamacion
{
    private $db;

    public function __construct()
    {
        $this->db = \Core\Database::getConexion();
    }

    public function guardar($nombre, $correo, $telefono, $mensaje)
    {
        $sql = "INSERT INTO reclamaciones (nombre, correo, telefono, mensaje)
                VALUES (:nombre, :correo, :telefono, :mensaje)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':mensaje' => $mensaje
        ]);
    }

    public function obtenerTodas()
    {
        $sql = "SELECT * FROM reclamaciones ORDER BY creado_en DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function eliminarPorId($id)
    {
        $sql = "DELETE FROM reclamaciones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
