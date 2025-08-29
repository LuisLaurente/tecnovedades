<?php

namespace Controllers;

use Models\Producto;
use Models\Promocion;
use Core\Helpers\PromocionHelper; // Importamos el helper de promociones

class CarritoController
{
public function agregar()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Sanitizar entrada
    $producto_id = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;
    $talla = isset($_POST['talla']) ? trim((string) $_POST['talla']) : null;
    $color = isset($_POST['color']) ? trim((string) $_POST['color']) : null;
    $cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 1;

    $referer = $_SERVER['HTTP_REFERER'] ?? url('carrito/ver');

    // Validaciones
    if ($producto_id <= 0) {
        $_SESSION['flash_error'] = 'Producto inválido.';
        header('Location: ' . $referer); exit;
    }
    if ($cantidad <= 0) {
        $_SESSION['flash_error'] = 'La cantidad debe ser al menos 1.';
        header('Location: ' . $referer); exit;
    }

    // Obtener producto
    $producto = \Models\Producto::obtenerPorId($producto_id);
    if (!$producto) {
        $_SESSION['flash_error'] = 'Producto no encontrado.';
        header('Location: ' . $referer); exit;
    }
    $precio = isset($producto['precio']) ? (float)$producto['precio'] : 0.0;
    $stock = isset($producto['stock']) && is_numeric($producto['stock']) ? (int)$producto['stock'] : null;

    if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) $_SESSION['carrito'] = [];

    $clave = $producto_id . '_' . ($talla ?? '') . '_' . ($color ?? '');
    $cantidadActual = isset($_SESSION['carrito'][$clave]['cantidad']) ? (int)$_SESSION['carrito'][$clave]['cantidad'] : 0;
    $nuevaCantidad = $cantidadActual + $cantidad;

    // Stock check
    if ($stock !== null && $stock >= 0) {
        if ($cantidadActual >= $stock) {
            $_SESSION['flash_error'] = 'No hay stock disponible para agregar más unidades.';
            header('Location: ' . $referer); exit;
        }
        if ($nuevaCantidad > $stock) {
            $cantidad = $stock - $cantidadActual;
            if ($cantidad <= 0) {
                $_SESSION['flash_error'] = 'No hay suficiente stock disponible.';
                header('Location: ' . $referer); exit;
            }
            $_SESSION['flash_warning'] = "Se agregaron solamente {$cantidad} unidades (stock limitado).";
            $nuevaCantidad = $cantidadActual + $cantidad;
        }
    }

    // Guardar en sesión (asegurando tipos)
    if (isset($_SESSION['carrito'][$clave])) {
        $_SESSION['carrito'][$clave]['cantidad'] = $nuevaCantidad;
    } else {
        $_SESSION['carrito'][$clave] = [
            'producto_id' => $producto_id,
            'talla' => $talla,
            'color' => $color,
            'cantidad' => $cantidad,
            'precio' => $precio
        ];
    }

    // Evaluar promociones si existe el helper (proteger fallos)
    try {
        if (class_exists('PromocionHelper')) {
            $_SESSION['promociones'] = PromocionHelper::evaluar($_SESSION['carrito'], $_SESSION['usuario'] ?? null);
        } elseif (class_exists('\Core\Helpers\PromocionHelper')) {
            $_SESSION['promociones'] = \Core\Helpers\PromocionHelper::evaluar($_SESSION['carrito'], $_SESSION['usuario'] ?? null);
        }
    } catch (\Throwable $e) {
        error_log('PromocionHelper::evaluar error: ' . $e->getMessage());
    }

    $_SESSION['mensaje_carrito'] = '✅ Agregado con éxito.';
    header('Location: ' . $referer);
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

        // Verificar si hay un cupón aplicado y agregarlo al descuento
        $cupon_aplicado = \Core\Helpers\CuponHelper::obtenerCuponAplicado();
        if ($cupon_aplicado) {
            $descuento_cupon = 0;
            if ($cupon_aplicado['tipo'] === 'porcentaje') {
                $descuento_cupon = $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
            } else {
                $descuento_cupon = min($cupon_aplicado['valor'], $totales['subtotal']);
            }
            $totales['descuento'] += $descuento_cupon;
            $totales['total'] = max($totales['subtotal'] - $totales['descuento'], 0);
            $totales['cupon_aplicado'] = $cupon_aplicado;
        }

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

        // Usuario puede ser null si es invitado
        $usuario = $_SESSION['usuario'] ?? null;

        // Calcular promociones para todos (invitados y logueados)
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
