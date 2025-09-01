<?php
namespace Controllers;

use Models\Review;

class ReviewController
{
    private $reviewModel;

    public function __construct($dbInstance)
    {
        // Asignar el modelo con la conexión a la BD
        $this->reviewModel = new Review($dbInstance);
    }

    // Guardar reseña enviada desde el modal
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                // 'producto_id' => $_POST['producto_id'] ?? null,
                'pedido_id' => $_POST['pedido_id'] ?? null,
                'user_id' => $_SESSION['user_id'] ?? 0, // Asegúrate de que el usuario esté logueado
                'puntuacion' => $_POST['puntuacion'] ?? 0,
                'titulo' => $_POST['titulo'] ?? '',
                'texto' => $_POST['texto'] ?? '',
            ];

            $resultado = $this->reviewModel->crear($data);

            if ($resultado) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error al guardar la reseña.";
            }
        }
    }

    // Listar todas las reseñas
    public function index()
    {
        $reseñas = $this->reviewModel->obtenerTodas();
        require_once __DIR__ . '/../views/review/index.php';
    }

    // Cambiar estado de la reseña (por ejemplo: activo/inactivo)
    public function cambiarEstado($id)
    {
        $res = $this->reviewModel->toggleEstado($id); // Asumiendo que tu modelo Review tiene este método
        if ($res) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            echo "Error al cambiar el estado de la reseña.";
        }
    }
}
