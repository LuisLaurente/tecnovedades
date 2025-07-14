<?php

namespace Controllers;

use Controllers\BaseController;
use \Models\Etiqueta;
use Core\Helpers\Validator;


class EtiquetaController extends BaseController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Etiqueta();
    }

    public function index()
    {
        $etiquetas = $this->modelo->obtenerTodas();
        $this->render('etiquetas/index', ['etiquetas' => $etiquetas]);
    }

    public function crear()
    {
        $errores = [];
        $nombre = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = Validator::validarTexto($_POST['nombre'], 'Nombre');
                $slug = Validator::generarSlug($nombre);
                $this->modelo->crear($nombre, $slug);
                header("Location: /TECNOVEDADES-MASTER/public/etiqueta/index");
exit;
            } catch (\Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        $this->render('etiquetas/crear', [
            'errores' => $errores,
            'nombre' => $nombre
        ]);
    }


    public function editar($id)
    {
        $errores = [];
        $etiqueta = $this->modelo->obtenerPorId($id);
        $nombre = $etiqueta['nombre'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = \Core\Helpers\Validator::validarTexto($_POST['nombre'], 'Nombre');
                $slug = \Core\Helpers\Validator::generarSlug($nombre);

                $this->modelo->actualizar($id, $nombre, $slug);
                header("Location: /TECNOVEDADES-MASTER/public/etiqueta/index");
                exit;
            } catch (\Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        $this->render('etiquetas/editar', [
            'etiqueta' => ['id' => $id, 'nombre' => $nombre],
            'errores' => $errores
        ]);
    }
    public function guardar()
{
    $nombre = $_POST['nombre'] ?? '';
    if (!$nombre) {
        echo "❌ Nombre vacío";
        return;
    }

    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $nombre), '-'));

    $db = \Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO etiquetas (nombre, slug) VALUES (?, ?)");
    $stmt->execute([$nombre, $slug]);

    // Redirige de vuelta a donde venías (por ejemplo, producto/editar)
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}




    public function eliminar($id)
    {
        $this->modelo->eliminar($id);


        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM etiquetas WHERE id = ?");
        $stmt->execute([$id]);

        // Redirigir al lugar indicado o a /etiqueta/index
        $redirect = $_GET['redirect'] ?? '/TECNOVEDADES-MASTER/public/etiqueta/index';
        header("Location: " . $redirect);
        exit;
    }
}
