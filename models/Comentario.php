<?php
namespace Models;

use Core\Database;
use PDO;

class Comentario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    /**
     * Crear un nuevo comentario
     */
    public function crear(array $data)
    {
        $sql = "INSERT INTO comentarios (producto_id, usuario_id, titulo, comentario, estrellas, fecha)
                VALUES (:producto_id, :usuario_id, :titulo, :comentario, :estrellas, :fecha)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':producto_id' => $data['producto_id'],
            ':usuario_id'  => $data['usuario_id'],
            ':titulo'      => $data['titulo'],
            ':comentario'  => $data['comentario'],
            ':estrellas'   => $data['estrellas'],
            ':fecha'       => $data['fecha']
        ]);
    }

    /**
     * Obtener comentarios de un producto
     */
    public function obtenerPorProducto($producto_id)
    {
        $sql = "SELECT c.*, u.nombre as usuario 
                FROM comentarios c
                INNER JOIN usuarios u ON u.id = c.usuario_id
                WHERE c.producto_id = :producto_id
                ORDER BY c.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':producto_id' => $producto_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener comentarios de un usuario
     */
    public function obtenerPorUsuario($usuario_id)
    {
        $sql = "SELECT * FROM comentarios 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
