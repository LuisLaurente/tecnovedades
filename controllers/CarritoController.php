<?php

namespace Controllers;

class CarritoController
{
    public function agregar()
    {
        $producto_id = $_POST['producto_id'];
        $talla = $_POST['talla'] ?? null;
        $color = $_POST['color'] ?? null;
        $cantidad = $_POST['cantidad'] ?? 1;

        // Obtener el precio actual del producto
        $producto = \Models\Producto::obtenerPorId($producto_id);
        $precio = $producto && isset($producto['precio']) ? $producto['precio'] : 0;

        $clave = $producto_id . '_' . $talla . '_' . $color;

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$clave] = [
                'producto_id' => $producto_id,
                'talla' => $talla,
                'color' => $color,
                'cantidad' => $cantidad,
                'precio' => $precio
            ];
        }

        // Guardar mensaje en sesión
        $_SESSION['mensaje_carrito'] = '✅ Agregado con éxito.';

        // Redirigir a la página anterior
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function eliminar($clave)
    {
        if (isset($_SESSION['carrito'][$clave])) {
            unset($_SESSION['carrito'][$clave]);
        }

        header('Location: /tecnovedades/public/carrito/ver');
    }

    public function ver()
    {
        $productosDetallados = [];
        $total = 0;

        if (!empty($_SESSION['carrito'])) {
            $productoModel = new \Models\Producto();

            foreach ($_SESSION['carrito'] as $clave => $item) {
                $producto = $productoModel->obtenerPorId($item['producto_id']);
                if ($producto) {
                    $producto['cantidad'] = $item['cantidad'];
                    $producto['talla'] = $item['talla'];
                    $producto['color'] = $item['color'];
                    $producto['clave'] = $clave;
                    $producto['subtotal'] = $producto['precio'] * $item['cantidad'];
                    $total += $producto['subtotal'];
                    $productosDetallados[] = $producto;
                }
            }
        }

        require __DIR__ . '/../views/carrito/ver.php';
    }

    public function aumentar($clave)
    {
        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad']++;
        }
        header('Location: /tecnovedades/public/carrito/ver');
    }

    public function disminuir($clave)
    {
        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad']--;
            if ($_SESSION['carrito'][$clave]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$clave]); // Eliminar si llega a 0
            }
        }
        header('Location: /tecnovedades/public/carrito/ver');
    }
}
