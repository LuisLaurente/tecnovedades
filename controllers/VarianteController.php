<?php

namespace Controllers;

use Models\VarianteProducto;

class VarianteController
{
    // 🛠️ Acción para actualizar una variante existente
    public function actualizar($id)
    {
        $producto_id = $_POST['producto_id'] ?? null;
        $talla = $_POST['talla'] ?? '';
        $color = $_POST['color'] ?? '';
        $stock = $_POST['stock'] ?? 0;

        if ($id) {
            VarianteProducto::actualizar($id, $talla, $color, $stock);
        }

        // Redirecciono nuevamente a la edición del producto
        header("Location: /producto/editar/$producto_id");
        exit;
    }
    // 🗑️ Acción para eliminar una variante
    public function eliminar($id)
    {
        // Llamo al modelo para eliminar la variante por ID
        \Models\VarianteProducto::eliminar($id);

        // Después de eliminar, redirijo a la página anterior (editar producto)
        // Primero obtengo el producto_id de esa variante
        $producto_id = $_GET['producto_id'] ?? null;

        if ($producto_id) {
            header("Location: /producto/editar/$producto_id");
        } else {
            // Si no hay producto_id, redirige al listado general
            header("Location: /producto");
        }
        exit;
    }
}
