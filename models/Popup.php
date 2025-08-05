<?php
namespace Models;

use Core\Database;
use PDO;

class Popup
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function obtener()
    {
        $stmt = $this->db->query("SELECT * FROM popup_promocional LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($texto, $imagen, $activo)
    {
        $sql = "UPDATE popup_promocional SET texto = :texto, imagen = :imagen, activo = :activo WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':texto' => $texto,
            ':imagen' => $imagen,
            ':activo' => $activo
        ]);
    }
}

