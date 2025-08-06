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
            // Eliminamos el archivo del servidor desde /uploads/
            $ruta = __DIR__ . '/../public/uploads/' . $imagen['nombre_imagen'];
            if (file_exists($ruta)) {
                unlink($ruta);
            }

            // Eliminamos el registro de la base de datos
            ImagenProducto::eliminar($id);
        }

        // ✅ Redireccionar de forma segura usando url() si no hay HTTP_REFERER
        if (!empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: ' . url('producto')); // fallback seguro
        }
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

        // ✅ Redirigir correctamente a la edición del producto con url()
        header("Location: " . url("producto/editar/$producto_id"));
        exit;
    }
}
