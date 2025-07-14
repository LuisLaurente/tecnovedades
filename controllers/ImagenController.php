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

    public function subir()
    {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo "❌ Error al subir la imagen.";
            return;
        }

        $producto_id = $_POST['producto_id'] ?? null;
        if (!$producto_id) {
            echo "❌ No se recibió ID de producto.";
            return;
        }

        $nombreOriginal = $_FILES['imagen']['name'];
        $nombreFinal = uniqid() . '_' . basename($nombreOriginal);
        $tmpPath = $_FILES['imagen']['tmp_name'];
        $destino = __DIR__ . '/../public/uploads/' . $nombreFinal;

        if (!move_uploaded_file($tmpPath, $destino)) {
            echo "❌ No se pudo mover la imagen al destino.";
            return;
        }

        // Guardar en base de datos
        \Models\ImagenProducto::guardar($producto_id, $nombreFinal);

        // Redirigir de nuevo a la edición del producto
        header("Location: /TECNOVEDADES-MASTER/public/producto/editar/$producto_id");
        exit;
    }
}
