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
                header("Location: /etiqueta");
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
                header("Location: /etiqueta");
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



    public function eliminar($id)
    {
        $this->modelo->eliminar($id);
        header("Location: /etiqueta/index");
        exit;
    }
}
