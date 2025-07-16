<?php

namespace Controllers;

use PDOException;

class CargaMasivaController
{
        public function index()
    {
        require __DIR__ . '/../views/carga/index.php';
    }
    public function descargarPlantilla()
    {
        $ruta = __DIR__ . '/../public/csv/plantilla_productos.csv';

        if (!file_exists($ruta)) {
            http_response_code(404);
            echo "Archivo no encontrado.";
            return;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="plantilla_productos.csv"');
        readfile($ruta);
    }
    public function procesarCSV()
    {
    
        
        if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
            echo "❌ Error al subir el archivo.";
            return;
        }

        $rutaTemporal = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($rutaTemporal, 'r');
        if (!$handle) {
            echo "❌ No se pudo leer el archivo.";
            return;
        }

        $db = \Core\Database::getInstance()->getConnection();
        $fila = 0;
        $reporteErrores  = [];
        $productosPendientes = [];
        while (($line = fgets($handle)) !== false) {
            $fila++;

            $datos = str_getcsv($line, ";");
            if ($fila === 1) continue; // saltar encabezado

            if (count($datos) < 11) continue;

            list($sku,$nombre, $descripcion, $precio, $stock, $visible, $categorias, $etiquetas, $talla, $color, $stockVariante, $imagenesNombres) = $datos;


            //validacion por fila del reporte de errores

            $errores  = [];

            if (!\Core\Helpers\Validator::isRequired($nombre)) {
                $errores[] = "Nombre vacío";
            }

            if (!\Core\Helpers\Validator::isRequired($descripcion)) {
                $errores[] = "Descripción vacía";
            }

            if (!\Core\Helpers\Validator::isNumeric($precio)) {
                $errores[] = "Precio inválido";
            }

            if (!ctype_digit($stock)) {
                $errores[] = "Stock debe ser entero";
            }

            if (!in_array($visible, ['0', '1'])) {
                $errores[] = "Visible debe ser 0 o 1";
            }

            // Validar stockVariante
            if (!ctype_digit($stockVariante)) {
                $errores[] = "Stock de variante inválido";
            }

            // Guardar los errores si existen
            if (!empty($errores)) {
                foreach ($errores as $error) {
                    $reporteErrores[] = [
                        'fila' => $fila,
                        'error' => $error
                    ];
                }
                continue; // no se inserta la fila
            }

            // 🔎 Buscar si existe producto por SKU
            $stmt = $db->prepare("SELECT id FROM productos WHERE sku = ?");
            $stmt->execute([$sku]);
            $productoExistente = $stmt->fetch();

            if ($productoExistente) {
                // Guardar para mostrar luego la confirmación
                $productosPendientes[] = [
                    'fila' => $fila,
                    'sku' => $sku,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'stock' => $stock,
                    'visible' => $visible,
                    'categorias' => $categorias,
                    'etiquetas' => $etiquetas,
                    'talla' => $talla,
                    'color' => $color,
                    'stockVariante' => $stockVariante,
                    'imagenesNombres' => $imagenesNombres
                ];
                continue; // 🔁 No actualices aún, espera confirmación
            }

            try {
            // Insertar nuevo producto con SKU
        $stmt = $db->prepare("INSERT INTO productos (sku, nombre, descripcion, precio, stock, visible)
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $visible]);
        $producto_id = $db->lastInsertId();

    // ✅ Insertar variante
    if (!empty($talla) && !empty($color) && ctype_digit($stockVariante)) {
        $stmtVar = $db->prepare("INSERT INTO variantes_producto (producto_id, talla, color, stock)
                                 VALUES (?, ?, ?, ?)");
        $stmtVar->execute([$producto_id, $talla, $color, $stockVariante]);
    }

    // ✅ Categorías
    $categoriasArr = explode(',', $categorias);
    foreach ($categoriasArr as $catNombre) {
        $catNombre = trim($catNombre);
        if (!$catNombre) continue;

        $stmtCat = $db->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $stmtCat->execute([$catNombre]);
        $cat = $stmtCat->fetch();

        $cat_id = $cat ? $cat['id'] : null;
        if (!$cat_id) {
            $stmtInsertCat = $db->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            $stmtInsertCat->execute([$catNombre]);
            $cat_id = $db->lastInsertId();
        }

        $stmtRelCat = $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)");
        $stmtRelCat->execute([$producto_id, $cat_id]);
    }

    // ✅ Etiquetas
    $etiquetasArr = explode(',', $etiquetas);
    foreach ($etiquetasArr as $etNombre) {
        $etNombre = trim($etNombre);
        if (!$etNombre) continue;

        $stmtEt = $db->prepare("SELECT id FROM etiquetas WHERE nombre = ?");
        $stmtEt->execute([$etNombre]);
        $et = $stmtEt->fetch();

        $et_id = $et ? $et['id'] : null;
        if (!$et_id) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $etNombre), '-'));
            $stmtInsertEt = $db->prepare("INSERT INTO etiquetas (nombre, slug) VALUES (?, ?)");
            $stmtInsertEt->execute([$etNombre, $slug]);
            $et_id = $db->lastInsertId();
        }

        $stmtRelEt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
        $stmtRelEt->execute([$producto_id, $et_id]);
    }

    // ✅ Imágenes
    if (!empty($imagenesNombres)) {
        $imagenesArr = array_map('trim', explode(',', $imagenesNombres));
        foreach ($imagenesArr as $nombreImg) {
            $rutaImg = __DIR__ . '/../public/uploads/' . $nombreImg;
            if (file_exists($rutaImg)) {
                \Models\ImagenProducto::guardar($producto_id, $nombreImg);
            } else {
                $reporteErrores[] = [
                    'fila' => $fila,
                    'error' => "Imagen '$nombreImg' no encontrada en /public/uploads/"
                ];
            }
        }
    }

} catch (\PDOException $e) {
    $reporteErrores[] = [
        'fila' => $fila,
        'error' => "❌ Error exacto en fila $fila: " . $e->getMessage()
    ];
    continue;
}

                
        }

        fclose($handle);

        //estructura base del reporte de errores
        if (!empty($reporteErrores)) {
            require __DIR__ . '/../views/carga/reporte.php';
            return;
        }
        //estructura base del reporte de errores; mostrar confirmacion al final del procesamiento
        if (!empty($productosPendientes)) {
            $_SESSION['productos_pendientes'] = $productosPendientes;
            require __DIR__ . '/../views/carga/confirmar_actualizacion.php';
            return;
        }


        // Redirigir al listado de productos
        header("Location: /TECNOVEDADES/public/producto/index");
        exit;
    }
    public function confirmarActualizacion()
{
    session_start(); // Asegúrate que esté por si se accede directo

    if (!isset($_POST['sobrescribir'])) {
        echo "No se seleccionó ningún producto para sobrescribir.";
        return;
    }

    $db = \Core\Database::getInstance()->getConnection();
    $productosPendientes = $_SESSION['productos_pendientes'] ?? [];
    $skusAActualizar = $_POST['sobrescribir'];

    foreach ($productosPendientes as $prod) {
        if (!in_array($prod['sku'], $skusAActualizar)) continue;

        $sku = $prod['sku'];

        // Buscar ID del producto existente
        $stmt = $db->prepare("SELECT id FROM productos WHERE sku = ?");
        $stmt->execute([$sku]);
        $productoExistente = $stmt->fetch();

        if (!$productoExistente) continue; // si no existe, ignorar

        $producto_id = $productoExistente['id'];

        // Actualizar producto
        $stmt = $db->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, visible = ? WHERE id = ?");
        $stmt->execute([
            $prod['nombre'],
            $prod['descripcion'],
            $prod['precio'],
            $prod['stock'],
            $prod['visible'],
            $producto_id
        ]);

        // Eliminar relaciones antiguas
        $db->prepare("DELETE FROM variantes_producto WHERE producto_id = ?")->execute([$producto_id]);
        $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$producto_id]);
        $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?")->execute([$producto_id]);
        $db->prepare("DELETE FROM imagenes_producto WHERE producto_id = ?")->execute([$producto_id]);

        // Insertar nueva variante
        if (!empty($prod['talla']) && !empty($prod['color']) && ctype_digit($prod['stockVariante'])) {
            $stmtVar = $db->prepare("INSERT INTO variantes_producto (producto_id, talla, color, stock) VALUES (?, ?, ?, ?)");
            $stmtVar->execute([$producto_id, $prod['talla'], $prod['color'], $prod['stockVariante']]);
        }

        // Insertar nuevas categorías
        $categorias = explode(',', $prod['categorias']);
        foreach ($categorias as $catNombre) {
            $catNombre = trim($catNombre);
            if (!$catNombre) continue;

            $stmt = $db->prepare("SELECT id FROM categorias WHERE nombre = ?");
            $stmt->execute([$catNombre]);
            $cat = $stmt->fetch();
            $cat_id = $cat ? $cat['id'] : null;

            if (!$cat_id) {
                $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->execute([$catNombre]);
                $cat_id = $db->lastInsertId();
            }

            $stmt = $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)");
            $stmt->execute([$producto_id, $cat_id]);
        }

        // Insertar nuevas etiquetas
        $etiquetas = explode(',', $prod['etiquetas']);
        foreach ($etiquetas as $etiquetaNombre) {
            $etiquetaNombre = trim($etiquetaNombre);
            if (!$etiquetaNombre) continue;

            $stmt = $db->prepare("SELECT id FROM etiquetas WHERE nombre = ?");
            $stmt->execute([$etiquetaNombre]);
            $et = $stmt->fetch();

            $et_id = $et ? $et['id'] : null;

            if (!$et_id) {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $etiquetaNombre), '-'));
                $stmt = $db->prepare("INSERT INTO etiquetas (nombre, slug) VALUES (?, ?)");
                $stmt->execute([$etiquetaNombre, $slug]);
                $et_id = $db->lastInsertId();
            }

            $stmt = $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$producto_id, $et_id]);
        }

        // Insertar imágenes
        $imagenesArr = explode(',', $prod['imagenesNombres']);
        foreach ($imagenesArr as $imgNombre) {
            $imgNombre = trim($imgNombre);
            $ruta = __DIR__ . '/../public/uploads/' . $imgNombre;
            if (file_exists($ruta)) {
                \Models\ImagenProducto::guardar($producto_id, $imgNombre);
            }
        }
    }

    // Limpiar sesión
    unset($_SESSION['productos_pendientes']);

    // Redirigir al listado
    header("Location: /TECNOVEDADES/public/producto/index");
    exit;
}


}