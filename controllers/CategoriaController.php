<?php

namespace Controllers;

use Models\Categoria;

class CategoriaController
{
    public function index()
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        $categorias = Categoria::obtenerTodas();
        require_once __DIR__ . '/../views/categoria/index.php';
    }

    public function crear()
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        $errores = [];
        $nombre = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');

            if (!\Core\Helpers\Validator::isRequired($nombre)) {
                $errores[] = "El nombre de la categoría es obligatorio.";
            }

            if (empty($errores)) {
                \Models\Categoria::crear($nombre);
                header('Location: ' . url('categoria'));
                exit;
            }
        }

        // Para mostrar formulario vacío o con errores
        require_once __DIR__ . '/../views/categoria/crear.php';
    }

    public function guardar()
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        $nombre = trim($_POST['nombre'] ?? '');
        $id_padre = $_POST['id_padre'] ?? '';
        if ($id_padre === '') $id_padre = null;

        try {
            Categoria::crear($nombre, $id_padre);
            header('Location: ' . url('categoria'));
            exit;
        } catch (\Exception $e) {
            // Volver a cargar el formulario con error
            $error = $e->getMessage();
            $categorias = Categoria::obtenerTodas();
            require_once __DIR__ . '/../views/categoria/crear.php';
        }
    }

    public function editar($id)
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        $categoria = Categoria::obtenerPorId($id);
        $categorias = Categoria::obtenerTodas();

        if (!$categoria) {
            echo "Categoría no encontrada.";
            return;
        }

        require_once __DIR__ . '/../views/categoria/editar.php';
    }

    public function actualizar()
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        $id = $_POST['id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $id_padre = $_POST['id_padre'] ?? null;
        if ($id_padre === '') $id_padre = null;

        try {
            if ($id) {
                Categoria::actualizar($id, $nombre, $id_padre);
            }
            header('Location: ' . url('categoria'));
            exit;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $categoria = Categoria::obtenerPorId($id);
            $categorias = Categoria::obtenerTodas();
            require_once __DIR__ . '/../views/categoria/editar.php';
        }
    }

    public function eliminar($id)
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php';
        if (Categoria::tieneHijos($id) || Categoria::tieneProductos($id)) {
            echo "<p style='color:red;'>No se puede eliminar esta categoría porque tiene subcategorías o productos asignados.</p>";
            echo "<p><a href='" . url('categoria') . "'>← Volver al listado</a></p>";
            return;
        }

        Categoria::eliminar($id);
        header('Location: ' . url('categoria'));
        exit;
    }
}
