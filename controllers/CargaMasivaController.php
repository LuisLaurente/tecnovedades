<?php

namespace Controllers;

class CargaMasivaController
{
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

        $fila = 0;
        while (($datos = fgetcsv($handle, 1000, ";")) !== false) {
            $fila++;

            // Ignorar la primera fila (cabeceras)
            // Si la primera fila es 0, significa que no hay datos
            if ($fila === 1) continue;

            if (count($datos) < 10) {
                echo "⚠️ Fila $fila inválida: columnas incompletas.<br>";
                continue;
            }

            list($nombre, $descripcion, $precio, $stock, $visible, $categorias, $etiquetas, $talla, $color, $stockVariante) = $datos;

            // Validaciones
            $errores = [];

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

            // Si hay errores, mostrar y saltar la fila
            if (!empty($errores)) {
                echo "❌ Error en fila $fila: " . implode(" | ", $errores) . "<br>";
                continue;
            }

            // Si pasa todas las validaciones
            echo "✅ Producto procesado en fila $fila: " . htmlspecialchars($nombre) . "<br>";

            // AQUÍ SE PONDRÁ LA BASE DE DATOS /////////////////////
            ////////////////////////////////////////////////
        }

        fclose($handle);
    }
}
