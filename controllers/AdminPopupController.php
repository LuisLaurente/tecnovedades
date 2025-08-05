<?php
namespace Controllers;

use Models\Popup;

class AdminPopupController
{
    public function index()
    {
        $popupModel = new Popup();
        $popup = $popupModel->obtener();

        require_once __DIR__ . '/../views/admin/popup/index.php';
    }

    public function guardar()
    {
        $texto = $_POST['texto'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        $popupModel = new Popup();
        $popupActual = $popupModel->obtener();
        $imagenFinal = $popupActual['imagen'];

        // Eliminar imagen si se seleccionó
        if (isset($_POST['eliminar_imagen']) && $imagenFinal) {
            $ruta = __DIR__ . '/../public/images/popup/' . $imagenFinal;
            if (file_exists($ruta)) {
                unlink($ruta);
            }
            $imagenFinal = null;
        }

        // Subir nueva imagen
        if (!empty($_FILES['nueva_imagen']['name'])) {
            $nombreTmp = $_FILES['nueva_imagen']['tmp_name'];
            $nombreArchivo = uniqid('popup_') . '_' . basename($_FILES['nueva_imagen']['name']);
            $destino = __DIR__ . '/../public/images/popup/' . $nombreArchivo;

            if (move_uploaded_file($nombreTmp, $destino)) {
                // Si hay imagen anterior y no fue eliminada manualmente, eliminarla
                if ($imagenFinal && file_exists(__DIR__ . '/../public/images/popup/' . $imagenFinal)) {
                    unlink(__DIR__ . '/../public/images/popup/' . $imagenFinal);
                }
                $imagenFinal = $nombreArchivo;
            }
        }

        // Guardar en base de datos
        $popupModel->actualizar($texto, $imagenFinal, $activo);

        header("Location: " . url("adminPopup/index"));
        exit;
    }
}
