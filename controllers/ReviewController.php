<?php
namespace Controllers;
use PDO;
class ReviewController
{
    public function index()
{
    $db = \Core\Database::getConexion();
    $stmt = $db->query("
        SELECT r.*, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
        FROM product_reviews r
        JOIN productos p ON r.producto_id = p.id
        JOIN usuarios u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include_once __DIR__ . '/../views/review/index.php';
}

public function aprobar($id)
{
    $db = \Core\Database::getConexion();
    $stmt = $db->prepare("UPDATE product_reviews SET estado = 'aprobado' WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['flash'] = "✅ Reseña aprobada con éxito";
    header("Location: " . url('review/index'));
    exit;
}

public function eliminar($id)
{
    $db = \Core\Database::getConexion();
    $stmt = $db->prepare("DELETE FROM product_reviews WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['flash'] = "🗑️ Reseña eliminada con éxito";
    header("Location: " . url('review/index'));
    exit;
}


}