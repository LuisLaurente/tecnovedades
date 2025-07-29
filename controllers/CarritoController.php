<?php

namespace Controllers;

use Models\Producto;
use Models\Promocion;
use Core\Helpers\PromocionHelper; // Importamos el helper de promociones

class CarritoController
{
    public function agregar()
    {
        $producto_id = $_POST['producto_id'];
        $talla = $_POST['talla'] ?? null;
        $color = $_POST['color'] ?? null;
        $cantidad = $_POST['cantidad'] ?? 1;

        // Obtener el precio actual del producto
        $producto = Producto::obtenerPorId($producto_id);
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

        // Evaluar promociones y actualizar sesión
        $usuario = $_SESSION['usuario'] ?? null;
        $_SESSION['promociones'] = PromocionHelper::evaluar($_SESSION['carrito'], $usuario);

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

        // Recalcular promociones tras eliminar
        $usuario = $_SESSION['usuario'] ?? null;
        $_SESSION['promociones'] = PromocionHelper::evaluar($_SESSION['carrito'] ?? [], $usuario);

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    public function ver()
    {
        $productosDetallados = [];
        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;
        // Evaluar promociones siempre que se cargue el carrito
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        if (!empty($carrito)) {
            $productoModel = new Producto();

            foreach ($carrito as $clave => $item) {
                $producto = $productoModel->obtenerPorId($item['producto_id']);
                if ($producto) {
                    $producto['cantidad'] = $item['cantidad'];
                    $producto['talla'] = $item['talla'];
                    $producto['color'] = $item['color'];
                    $producto['clave'] = $clave;
                    $producto['subtotal'] = $producto['precio'] * $item['cantidad'];
                    $productosDetallados[] = $producto;
                }
            }
        }

        // Hacemos disponibles las variables en la vista
        $promocionesAplicadas = $promociones;
        require __DIR__ . '/../views/carrito/ver.php';

    }

    public function aumentar($clave)
    {
        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad']++;
        }

        // Recalcular promociones
        $usuario = $_SESSION['usuario'] ?? null;
        $_SESSION['promociones'] = PromocionHelper::evaluar($_SESSION['carrito'], $usuario);

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    public function disminuir($clave)
    {
        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad']--;
            if ($_SESSION['carrito'][$clave]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$clave]);
            }
        }

        // Recalcular promociones
        $usuario = $_SESSION['usuario'] ?? null;
        $_SESSION['promociones'] = PromocionHelper::evaluar($_SESSION['carrito'] ?? [], $usuario);

        header('Location: ' . url('carrito/ver'));
        exit;
    }
}
