<?php

namespace Controllers;

use Models\VarianteProducto;

class VarianteController
{
    // 🛠️ Acción para actualizar una variante existente
    public function actualizar($id)
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php'; // Aseguramos el helper disponible

        $producto_id = $_POST['producto_id'] ?? null;
        $talla = $_POST['talla'] ?? '';
        $color = $_POST['color'] ?? '';
        $stock = $_POST['stock'] ?? 0;

        if ($id) {
            VarianteProducto::actualizar($id, $talla, $color, $stock);
        }

        // Redirecciono nuevamente a la edición del producto con url()
        header('Location: ' . url("producto/editar/$producto_id"));
        exit;
    }

    // 🗑️ Acción para eliminar una variante
    public function eliminar($id)
    {
        require_once __DIR__ . '/../Core/helpers/urlHelper.php'; // Aseguramos el helper disponible

        // Llamo al modelo para eliminar la variante por ID
        \Models\VarianteProducto::eliminar($id);

        // Después de eliminar, redirijo a la edición del producto si existe el producto_id
        $producto_id = $_GET['producto_id'] ?? null;

        if ($producto_id) {
            header('Location: ' . url("producto/editar/$producto_id"));
        } else {
            // Si no hay producto_id, redirige al listado general de productos
            header('Location: ' . url('producto'));
        }
        exit;
    }
}
