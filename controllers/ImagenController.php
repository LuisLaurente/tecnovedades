<?php

namespace Controllers;

use Models\ImagenProducto;

class ImagenController
{
    public function eliminar($id)
    {
        // Obtenemos el nombre del archivo para borrarlo físicamente
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT nombre_imagen FROM imagenes_producto WHERE id = ?");
        $stmt->execute([$id]);
        $imagen = $stmt->fetch();

        if ($imagen) {
            //Eliminamos el archivo del servidor desde /uploads/
            $ruta = __DIR__ . '/../public/uploads/' . $imagen['nombre_imagen'];
            if (file_exists($ruta)) {
                unlink($ruta);
            }

            // Eliminamos el registro de la base de datos
            ImagenProducto::eliminar($id);
        }

        // Redireccionar a la página anterior
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
