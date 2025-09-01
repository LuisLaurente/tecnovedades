<?php

namespace Controllers;

use Models\Producto;
use Models\ImagenProducto;
// Asegúrate de que los namespaces de tus helpers son correctos.
// Si están en la raíz del namespace Core\Helpers, esta es la forma correcta.
use Core\Helpers\PromocionHelper;
use Core\Helpers\CuponHelper;

class CarritoController
{
    // --- MÉTODOS PRIVADOS DE AYUDA PARA AJAX Y LÓGICA CENTRALIZADA ---

    /**
     * Verifica si la petición actual es una petición AJAX.
     * Esencial para diferenciar entre una recarga de página y una llamada de JavaScript.
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Prepara y envía una respuesta JSON estandarizada y finaliza la ejecución del script.
     * Garantiza que todas las respuestas AJAX tengan un formato consistente.
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        // Asegurarse de que no haya salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
        exit; // ¡Crucial! Detiene el script para no enviar HTML extra.
    }

    /**
     * Obtiene el estado completo y actualizado del carrito.
     * Centraliza toda la lógica de cálculo (promociones, cupones, totales)
     * para ser usada tanto por las vistas normales como por las respuestas AJAX.
     */
    private function getCartState(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;

        // 1. Evaluar promociones
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $_SESSION['promociones'] = $promociones;

        // 2. Calcular totales base con promociones
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        // 3. Aplicar cupón si existe y es válido
        $cupon_aplicado = CuponHelper::obtenerCuponAplicado();
        $descuento_cupon = 0;

        if ($cupon_aplicado && !empty($carrito) && $usuario) {
            $productosParaValidacion = $this->getProductosDetalladosParaValidacion($carrito);
            $aplicacionCupon = CuponHelper::aplicarCupon(
                $cupon_aplicado['codigo'],
                $usuario['id'],
                $carrito,
                $productosParaValidacion
            );

            if ($aplicacionCupon['exito']) {
                $descuento_cupon = $aplicacionCupon['descuento'];
            } else {
                CuponHelper::limpiarCuponSesion();
                $cupon_aplicado = null;
            }
        } elseif ($cupon_aplicado && !$usuario) {
            // Limpiar cupón si no hay sesión
            CuponHelper::limpiarCuponSesion();
            $cupon_aplicado = null;
        }

        // 4. Recalcular totales finales con el descuento del cupón
        $totales['descuento_cupon'] = $descuento_cupon;
        $totales['total'] = max(0, ($totales['subtotal'] ?? 0) - ($totales['descuento'] ?? 0) - $descuento_cupon);

        // 5. Obtener detalles de los productos para la respuesta
        $productosDetallados = [];
        if (!empty($carrito)) {
            foreach ($carrito as $clave => $item) {
                $producto = Producto::obtenerPorId($item['producto_id']);
                if ($producto) {
                    // ✅ Traer primera imagen
                    $primera = ImagenProducto::obtenerPrimeraPorProducto((int)$item['producto_id']);
                    $imagenUrl = ($primera && !empty($primera['nombre_imagen']))
                        ? url('uploads/' . $primera['nombre_imagen'])
                        : null;
                    // Preparamos un array limpio para la respuesta JSON
                    $productosDetallados[$clave] = [
                        'clave' => $clave,
                        'producto_id' => $item['producto_id'],
                        'nombre' => $producto['nombre'],
                        'cantidad' => $item['cantidad'],
                        'precio' => (float)$item['precio'],
                        'subtotal' => (float)$item['precio'] * $item['cantidad'],
                        'imagen'       => $imagenUrl, 
                    ];
                }
            }
        }

        // 6. Devolver el estado completo
        return [
            'items' => array_values($productosDetallados), // Array indexado para iterar en JS
            'itemDetails' => $productosDetallados,         // Array asociativo para búsquedas por clave
            'totals' => $totales,
            'promotions' => $promociones,
            'coupon' => $cupon_aplicado,
            'itemCount' => array_sum(array_column($carrito, 'cantidad'))
        ];
    }

    /**
     * Función auxiliar para obtener los modelos de producto para la validación de cupones.
     */
    private function getProductosDetalladosParaValidacion(array $carrito): array
    {
        $productos = [];
        if (empty($carrito)) return $productos;

        foreach ($carrito as $item) {
            $producto = Producto::obtenerPorId($item['producto_id']);
            if ($producto) {
                $productos[] = $producto;
            }
        }
        return $productos;
    }

    // --- MÉTODOS PÚBLICOS DEL CONTROLADOR (ACCIONES) ---

    /**
     * Aumenta la cantidad de un producto.
     * Responde con JSON si es AJAX, de lo contrario redirige.
     */
    public function aumentar($clave)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['carrito'][$clave])) {
            $item = $_SESSION['carrito'][$clave];
            $producto = Producto::obtenerPorId($item['producto_id']);
            $stock = isset($producto['stock']) && is_numeric($producto['stock']) ? (int)$producto['stock'] : null;

            // Verificación de stock antes de aumentar
            if ($stock !== null && $item['cantidad'] >= $stock) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(false, 'No hay más stock disponible para este producto.', $this->getCartState());
                }
                $_SESSION['flash_error'] = 'No hay más stock disponible para este producto.';
                header('Location: ' . url('carrito/ver'));
                exit;
            }

            $_SESSION['carrito'][$clave]['cantidad']++;
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, 'Cantidad aumentada.', $this->getCartState());
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    /**
     * Disminuye la cantidad de un producto. Si llega a 0, lo elimina.
     * Responde con JSON si es AJAX, de lo contrario redirige.
     */
    public function disminuir($clave)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['carrito'][$clave])) {
            $_SESSION['carrito'][$clave]['cantidad']--;
            if ($_SESSION['carrito'][$clave]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$clave]);
            }
        }

        // Si el carrito queda vacío, limpiar el cupón
        if (empty($_SESSION['carrito'])) {
            CuponHelper::limpiarCuponSesion();
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, 'Cantidad disminuida.', $this->getCartState());
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    /**
     * Elimina un producto del carrito.
     * Responde con JSON si es AJAX, de lo contrario redirige.
     */
    public function eliminar($clave)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['carrito'][$clave])) {
            unset($_SESSION['carrito'][$clave]);
        }

        // Si el carrito queda vacío, limpiar el cupón
        if (empty($_SESSION['carrito'])) {
            CuponHelper::limpiarCuponSesion();
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, 'Producto eliminado.', $this->getCartState());
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    /**
     * Agrega un producto al carrito.
     * Este método mantiene la redirección, ya que se suele llamar desde la página de un producto.
     */
    public function agregar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $producto_id = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;
        $talla = isset($_POST['talla']) ? trim((string) $_POST['talla']) : null;
        $color = isset($_POST['color']) ? trim((string) $_POST['color']) : null;
        $cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 1;
        $referer = $_SERVER['HTTP_REFERER'] ?? url('carrito/ver');

        if ($producto_id <= 0 || $cantidad <= 0) {
            $_SESSION['flash_error'] = 'Datos de producto inválidos.';
            header('Location: ' . $referer);
            exit;
        }

        $producto = Producto::obtenerPorId($producto_id);
        if (!$producto) {
            $_SESSION['flash_error'] = 'Producto no encontrado.';
            header('Location: ' . $referer);
            exit;
        }

        $precio = (float)($producto['precio'] ?? 0.0);
        $stock = is_numeric($producto['stock'] ?? null) ? (int)$producto['stock'] : null;
        $clave = $producto_id . '_' . ($talla ?? '') . '_' . ($color ?? '');

        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
        $cantidadActual = $_SESSION['carrito'][$clave]['cantidad'] ?? 0;
        $nuevaCantidad = $cantidadActual + $cantidad;

        if ($stock !== null) {
            if ($nuevaCantidad > $stock) {
                $cantidad = max(0, $stock - $cantidadActual);
                if ($cantidad > 0) {
                    $_SESSION['flash_warning'] = "Stock limitado. Solo se agregaron {$cantidad} unidades.";
                    $nuevaCantidad = $stock;
                } else {
                    $_SESSION['flash_error'] = 'No hay más stock disponible para este producto.';
                    header('Location: ' . $referer);
                    exit;
                }
            }
        }

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

        $_SESSION['mensaje_carrito'] = '✅ Agregado con éxito.';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Muestra la vista del carrito para usuarios con sesión iniciada.
     */
    public function ver()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario'])) {
            return $this->verSinSesion();
        }

        $cartState = $this->getCartState();
        $productosDetallados = [];
        $carrito = $_SESSION['carrito'] ?? [];

        if (!empty($carrito)) {
            foreach ($carrito as $clave => $item) {
                $producto = Producto::obtenerPorId($item['producto_id']);
                if ($producto) {
                    $producto['cantidad'] = $item['cantidad'];
                    $producto['talla'] = $item['talla'];
                    $producto['color'] = $item['color'];
                    $producto['clave'] = $clave;
                    $producto['subtotal'] = $producto['precio'] * $item['cantidad'];

                    $primera = ImagenProducto::obtenerPrimeraPorProducto((int)$item['producto_id']);
                    $producto['imagen'] = ($primera && !empty($primera['nombre_imagen']))
                        ? url('uploads/' . $primera['nombre_imagen'])
                        : null;
                    $productosDetallados[] = $producto;
                }
            }
        }

        $totales = $cartState['totals'];
        $promocionesAplicadas = $cartState['promotions'];
        $cupon_aplicado = $cartState['coupon'];

        require __DIR__ . '/../views/carrito/ver.php';
    }

    /**
     * Muestra la vista del carrito para usuarios sin sesión (invitados).
     */
    public function verSinSesion()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $cartState = $this->getCartState();
        $productosDetallados = [];
        $carrito = $_SESSION['carrito'] ?? [];

        if (!empty($carrito)) {
            foreach ($carrito as $clave => $item) {
                $producto = Producto::obtenerPorId($item['producto_id']);
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

        $totales = $cartState['totals'];
        $promocionesAplicadas = $cartState['promotions'];
        $error = $_SESSION['auth_error'] ?? null;
        if ($error) unset($_SESSION['auth_error']);

        require __DIR__ . '/../views/carrito/ver-sin-sesion.php';
    }

    /**
     * Redirige al checkout o a la vista de login dependiendo del estado de la sesión.
     */
    public function finalizarCompra()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['carrito'])) {
            $_SESSION['flash_error'] = 'Tu carrito está vacío.';
            header('Location: ' . url('/'));
            exit;
        }

        if (isset($_SESSION['usuario'])) {
            header('Location: ' . url('pedido/checkout'));
        } else {
            header('Location: ' . url('carrito/ver'));
        }
        exit;
    }

    /**
     * Aplica un cupón de descuento al carrito.
     */
    public function aplicarCupon()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('carrito/ver'));
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();

        $codigo = trim($_POST['codigo'] ?? '');
        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;

        if (empty($codigo)) {
            $_SESSION['mensaje_cupon_error'] = 'Código de cupón requerido.';
        } elseif (empty($carrito)) {
            $_SESSION['mensaje_cupon_error'] = 'El carrito está vacío.';
        } elseif (!$usuario) {
            $_SESSION['mensaje_cupon_error'] = 'Debes iniciar sesión para aplicar un cupón.';
        } else {
            $productosParaValidacion = $this->getProductosDetalladosParaValidacion($carrito);
            $resultado = CuponHelper::aplicarCupon($codigo, $usuario['id'], $carrito, $productosParaValidacion);

            if ($resultado['exito']) {
                $_SESSION['cupon_aplicado'] = $resultado['cupon'];
                $_SESSION['mensaje_cupon_exito'] = $resultado['mensaje'];
            } else {
                $_SESSION['mensaje_cupon_error'] = $resultado['mensaje'];
            }
        }

        header('Location: ' . url('carrito/ver'));
        exit;
    }

    /**
     * Quita cualquier cupón aplicado del carrito.
     */
    public function quitarCupon()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        CuponHelper::limpiarCuponSesion();
        $_SESSION['mensaje_cupon_exito'] = 'Cupón removido correctamente.';
        header('Location: ' . url('carrito/ver'));
        exit;
    }
}
