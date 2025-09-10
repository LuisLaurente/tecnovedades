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
            $ruta = dirname(__DIR__, 2) . '/public/uploads/' . $imagen['nombre_imagen'];
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
    echo "<pre>";

    // 1️⃣ Ver todo el array de archivos subidos
    echo "=== DEBUG _FILES ===\n";
    var_dump($_FILES);

    // 2️⃣ Ver todo el POST
    echo "\n=== DEBUG _POST ===\n";
    var_dump($_POST);

    // 3️⃣ Validación inicial
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo "\n❌ Error: no se recibió la imagen o hubo un error al subir.";
        exit;
    }

    $producto_id = $_POST['producto_id'] ?? null;
    if (!$producto_id) {
        echo "\n❌ Error: no se recibió ID de producto.";
        exit;
    }

    $nombreOriginal = $_FILES['imagen']['name'];
    $nombreFinal = uniqid() . '_' . basename($nombreOriginal);
    $tmpPath = $_FILES['imagen']['tmp_name'];

    // 4️⃣ Ruta destino
    $destino = dirname(__DIR__, 2) . '/public/uploads/' . $nombreFinal;

    echo "\n=== DEBUG RUTAS ===\n";
    echo "TmpPath: $tmpPath\n";
    echo "Destino: $destino\n";

    // 5️⃣ Probar movimiento
    if (!move_uploaded_file($tmpPath, $destino)) {
        echo "\n❌ No se pudo mover la imagen al destino.";
        exit;
    }

    echo "\n✅ Imagen movida correctamente.\n";

    // Guardar en base de datos
    \Models\ImagenProducto::guardar($producto_id, $nombreFinal);

    echo "✅ Guardada en BD con nombre: $nombreFinal\n";

    echo "</pre>";
    exit;
}

}
