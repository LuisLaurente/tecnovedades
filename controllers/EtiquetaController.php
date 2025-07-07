<?php

namespace Controllers;
use Controllers\BaseController;
use \Models\Etiqueta;

require_once 'models/Etiqueta.php';
require_once 'core/validar_input.php';


class EtiquetaController extends BaseController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Etiqueta();
    }

    public function index() {
        $etiquetas = $this->modelo->obtenerTodas();
        $this->render('etiquetas/index', ['etiquetas' => $etiquetas]);
        
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = validarTexto($_POST['nombre'], 'Nombre');
            $slug = generarSlug($nombre);
            $this->modelo->crear($nombre, $slug);
            header("Location: /TECNOVEDADES-MASTER/index.php?url=etiqueta/index");
            exit;
        }
        $this->render('etiquetas/crear');
    }

    public function editar($id) {
        $etiqueta = $this->modelo->obtenerPorId($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = validarTexto($_POST['nombre'], 'Nombre');
            $slug = generarSlug($nombre);
            $this->modelo->actualizar($id, $nombre, $slug);
            header("Location: /TECNOVEDADES-MASTER/index.php?url=etiqueta/index");
            exit;
        }
        $this->render('etiquetas/editar', ['etiqueta' => $etiqueta]);

        
    }
    

    public function eliminar($id) {
        $this->modelo->eliminar($id);
        header("Location: /TECNOVEDADES-MASTER/index.php?url=etiqueta/index");
        exit;
    }
    
}