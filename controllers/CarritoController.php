<?php

namespace Controllers;

use Models\Producto;
use Models\Promocion;
use Core\Helpers\PromocionHelper; // Importamos el helper de promociones
use Core\Helpers\CuponHelper; // Importamos el helper de cupones

class CarritoController
{
/**
 * NUEVA FUNCIONALIDAD: Carrito con Login Integrado
 * 
 * Cuando un usuario no tiene sesión iniciada, el método ver() 
 * automáticamente redirige a verSinSesion() que muestra:
 * - Lista de productos del carrito
 * - Formulario de login integrado 
 * - Botones de registro y redes sociales
 * - Resumen de compra en columna derecha
 * 
 * Esto elimina la necesidad de usar precheckout.php y mejora 
 * la experiencia de usuario manteniendo todo en una sola vista.
 */
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

        // Si el carrito quedó vacío, limpiar cupón
        if (empty($_SESSION['carrito'])) {
            CuponHelper::limpiarCuponSesion();
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    public function ver()
    {
        // Si no hay sesión iniciada, mostrar vista especial con login
        if (!isset($_SESSION['usuario'])) {
            return $this->verSinSesion();
        }

        $productosDetallados = [];
        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;
        
        // Evaluar promociones siempre que se cargue el carrito
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        // Verificar si hay un cupón aplicado usando CuponHelper
        $cupon_aplicado = CuponHelper::obtenerCuponAplicado();
        $descuento_cupon = 0;
        
        if ($cupon_aplicado && !empty($carrito)) {
            // Obtener información de los productos para la validación completa
            $productosParaValidacion = [];
            foreach ($carrito as $item) {
                $producto = Producto::obtenerPorId($item['producto_id']);
                if ($producto) {
                    $productosParaValidacion[] = $producto;
                }
            }
            
            // Usar CuponHelper para validar y calcular el descuento
            $cliente_id = $usuario['id'] ?? 1; // Usar ID del usuario o valor por defecto
            $aplicacionCupon = CuponHelper::aplicarCupon(
                $cupon_aplicado['codigo'], 
                $cliente_id, 
                $carrito, 
                $productosParaValidacion
            );
            
            if ($aplicacionCupon['exito']) {
                $descuento_cupon = $aplicacionCupon['descuento'];
                $totales['descuento_cupon'] = $descuento_cupon;
                $totales['total'] = max($totales['subtotal'] - $totales['descuento'] - $descuento_cupon, 0);
            } else {
                // Si el cupón ya no es válido, removerlo
                CuponHelper::limpiarCuponSesion();
                $cupon_aplicado = null;
            }
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

    /**
     * Vista del carrito cuando no hay sesión iniciada
     * Muestra productos del carrito + formulario de login integrado
     */
    public function verSinSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $productosDetallados = [];
        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = null; // Usuario no autenticado
        
        // Evaluar promociones para mostrar precios correctos
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        // Los cupones no se aplican sin sesión, pero verificamos si hay alguno guardado
        $cupon_aplicado = CuponHelper::obtenerCuponAplicado();
        $descuento_cupon = 0;
        
        // Si hay un cupón en sesión pero no hay usuario, lo limpiamos
        if ($cupon_aplicado && !$usuario) {
            CuponHelper::limpiarCuponSesion();
            $cupon_aplicado = null;
        }

        // Obtener detalles de los productos del carrito
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

        // Obtener mensajes de error si los hay (desde login fallido)
        $error = $_SESSION['auth_error'] ?? null;
        if ($error) {
            unset($_SESSION['auth_error']);
        }

        // Hacemos disponibles las variables en la vista
        $promocionesAplicadas = $promociones;
        require __DIR__ . '/../views/carrito/ver-sin-sesion.php';
    }

    /**
     * Procesar finalizar compra - redirige a login o checkout según sesión
     */
    public function finalizarCompra()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $carrito = $_SESSION['carrito'] ?? [];
        
        // Verificar que hay productos en el carrito
        if (empty($carrito)) {
            $_SESSION['flash_error'] = 'Tu carrito está vacío.';
            header('Location: ' . url('/'));
            exit;
        }

        // Si hay sesión, ir directamente al checkout
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . url('pedido/checkout'));
            exit;
        }

        // Si no hay sesión, mostrar la vista con login
        header('Location: ' . url('carrito/ver'));
        exit;
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

        // Si el carrito quedó vacío, limpiar cupón
        if (empty($_SESSION['carrito'])) {
            CuponHelper::limpiarCuponSesion();
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    // Métodos para manejar cupones en el carrito
    public function aplicarCupon()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('carrito/ver'));
            exit;
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $carrito = $_SESSION['carrito'] ?? [];
        
        if (empty($codigo)) {
            $_SESSION['mensaje_cupon_error'] = 'Código de cupón requerido';
            header('Location: ' . url('carrito/ver'));
            exit;
        }

        if (empty($carrito)) {
            $_SESSION['mensaje_cupon_error'] = 'El carrito está vacío';
            header('Location: ' . url('carrito/ver'));
            exit;
        }

        $usuario = $_SESSION['usuario'] ?? null;
        $cliente_id = $usuario['id'] ?? 1;
        
        // Obtener productos detallados para validación
        $productosParaValidacion = [];
        foreach ($carrito as $item) {
            $producto = Producto::obtenerPorId($item['producto_id']);
            if ($producto) {
                $productosParaValidacion[] = $producto;
            }
        }
        
        $resultado = CuponHelper::aplicarCupon($codigo, $cliente_id, $carrito, $productosParaValidacion);
        
        if ($resultado['exito']) {
            $_SESSION['cupon_aplicado'] = $resultado['cupon'];
            $_SESSION['mensaje_cupon_exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['mensaje_cupon_error'] = $resultado['mensaje'];
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    public function quitarCupon()
    {
        CuponHelper::limpiarCuponSesion();
        $_SESSION['mensaje_cupon_exito'] = 'Cupón removido correctamente';
        header('Location: ' . url('carrito/ver'));
        exit;
    }
}
