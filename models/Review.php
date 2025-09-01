<?php
namespace Models;

use PDO;
use Core\Database;
class Review
{
    private $db;

    public function __construct($db)
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Guardar reseña
    public function crear($data)
    {
        $sql = "INSERT INTO product_reviews 
                ( user_id, pedido_id, puntuacion, titulo, texto, created_at)
                VALUES (:user_id, :pedido_id, :puntuacion, :titulo, :texto, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            
            ':user_id'     => $data['user_id'],
            ':pedido_id'    => $data['pedido_id'],
            ':puntuacion'  => $data['puntuacion'],
            ':titulo'      => $data['titulo'],
            ':texto'       => $data['texto']
        ]);
    }

    // Obtener todas las reseñas
    public function obtenerTodas()
    {
        $sql = "SELECT r.*, 
                       u.nombre AS usuario_nombre, 
                       p.nombre AS producto_nombre 
                FROM product_reviews r
                JOIN usuarios u ON r.user_id = u.id
                
                ORDER BY r.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function toggleEstado($id)
    {
        // Obtenemos el estado actual
        $stmt = $this->db->prepare("SELECT estado FROM reviews WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $review = $stmt->fetch();

        if (!$review) {
            return false; // No existe la reseña
        }

        // Alternar estado (si es 1 -> 0, si es 0 -> 1)
        $nuevoEstado = $review['estado'] == 1 ? 0 : 1;

        // Actualizar en la BD
        $update = $this->db->prepare("UPDATE reviews SET estado = :estado WHERE id = :id");
        return $update->execute([
            ':estado' => $nuevoEstado,
            ':id' => $id
        ]);
    }

}
