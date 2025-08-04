<?php

namespace Controllers;

use Models\Pedido;
use Models\Cliente;
use Models\DetallePedido;
use Core\Helpers\PromocionHelper;
use Core\Helpers\CuponHelper;

class PedidoController
{
    private $pedidoModel;
    private $clienteModel;
    private $detalleModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->clienteModel = new Cliente();
        $this->detalleModel = new DetallePedido();
    }

    // Muestra formulario de checkout
    public function checkout()
    {
        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;

        // Evaluar promociones nuevamente antes de procesar pedido
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        require __DIR__ . '/../views/pedido/checkout.php';
    }

    // Procesa y guarda el pedido completo
    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
            $errores = [];

            // Validaciones básicas
            if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
            if ($direccion === '') $errores[] = 'La dirección es obligatoria.';
            if ($telefono === '' && $correo === '') {
                $errores[] = 'Debe ingresar teléfono o correo.';
            }
            if ($telefono !== '' && !preg_match('/^\d+$/', $telefono)) {
                $errores[] = 'El teléfono solo debe contener números.';
            }
            if (empty($carrito)) $errores[] = 'El carrito está vacío.';

            // Validar que todos los productos tengan precio
            foreach ($carrito as $item) {
                if (!isset($item['precio'])) {
                    $errores[] = 'Falta el precio de un producto en el carrito. Vuelve a agregar los productos.';
                    break;
                }
            }

            if (!empty($errores)) {
                $_SESSION['errores_checkout'] = $errores;
                header('Location: ' . url('pedido/checkout'));
                exit;
            }

            // Verificar si el cliente ya existe o crear uno nuevo
            $clienteExistente = null;
            if (!empty($correo)) {
                $clienteExistente = $this->clienteModel->obtenerPorCorreo($correo);
            }
            
            if ($clienteExistente) {
                $cliente_id = $clienteExistente['id'];
            } else {
                // Crear cliente nuevo
                $cliente_id = $this->clienteModel->crear($nombre, $direccion, $telefono, $correo);
                if (!$cliente_id) {
                    $_SESSION['errores_checkout'] = ['No se pudo registrar el cliente.'];
                    header('Location: ' . url('pedido/checkout'));
                    exit;
                }
            }

            // Calcular subtotal y aplicar promociones
            $usuario = $_SESSION['usuario'] ?? null;
            $promociones = PromocionHelper::evaluar($carrito, $usuario);
            $totales = PromocionHelper::calcularTotales($carrito, $promociones);

            // Aplicar cupón si existe en la sesión
            $cupon_aplicado = CuponHelper::obtenerCuponAplicado();
            $descuento_cupon = 0;
            $cupon_id = null;
            
            if ($cupon_aplicado) {
                // Verificar si hay restricciones por cliente específico
                if (!empty($cupon_aplicado['usuarios_autorizados'])) {
                    // Si hay restricciones, verificar si el cliente existe y está autorizado
                    if ($clienteExistente) {
                        // Cliente existe, verificar si está autorizado
                        $autorizados = json_decode($cupon_aplicado['usuarios_autorizados'], true);
                        
                        // Validar que el json_decode fue exitoso y devolvió un array
                        if (!is_array($autorizados)) {
                            CuponHelper::limpiarCuponSesion();
                            $_SESSION['errores_checkout'] = ['Error en la configuración del cupón.'];
                            header('Location: ' . url('pedido/checkout'));
                            exit;
                        }
                        
                        $autorizados = array_map('intval', $autorizados);
                        
                        if (!in_array((int)$cliente_id, $autorizados)) {
                            // Cliente existe pero no está autorizado
                            CuponHelper::limpiarCuponSesion();
                            $_SESSION['errores_checkout'] = ['Este cupón no está disponible para tu cuenta.'];
                            header('Location: ' . url('pedido/checkout'));
                            exit;
                        }
                        // Cliente autorizado, continuar con la aplicación del cupón
                        $cliente_id_para_cupon = $cliente_id;
                    } else {
                        // Cliente nuevo (no existe), no puede usar cupón restringido
                        CuponHelper::limpiarCuponSesion();
                        $_SESSION['errores_checkout'] = ['Este cupón es solo para clientes específicos. Tu correo no está en la lista de clientes autorizados.'];
                        header('Location: ' . url('pedido/checkout'));
                        exit;
                    }
                } else {
                    // Cupón sin restricciones, usar el cliente_id final
                    $cliente_id_para_cupon = $cliente_id;
                }
                
                // Aplicar el cupón
                $aplicacion = CuponHelper::aplicarCupon($cupon_aplicado['codigo'], $cliente_id_para_cupon, $carrito);
                if ($aplicacion['exito']) {
                    $descuento_cupon = $aplicacion['descuento'];
                    $cupon_id = $aplicacion['cupon']['id'];
                    $totales['descuento'] += $descuento_cupon;
                    $totales['total'] = max($totales['subtotal'] - $totales['descuento'], 0);
                } else {
                    // Si el cupón ya no es válido para este cliente, limpiar sesión y mostrar error
                    CuponHelper::limpiarCuponSesion();
                    $_SESSION['errores_checkout'] = [$aplicacion['mensaje']];
                    header('Location: ' . url('pedido/checkout'));
                    exit;
                }
            }

            // Crear pedido usando el total calculado
            $pedido_id = $this->pedidoModel->crear($cliente_id, $totales['total']);
            if (!$pedido_id) {
                $_SESSION['errores_checkout'] = ['No se pudo registrar el pedido.'];
                header('Location: ' . url('pedido/checkout'));
                exit;
            }

            // Guardar detalle del pedido
            $falloDetalle = false;
            foreach ($carrito as $item) {
                $ok = $this->detalleModel->crear(
                    $pedido_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['precio'],
                    $item['variante_id'] ?? null
                );
                if (!$ok) $falloDetalle = true;
            }
            if ($falloDetalle) {
                $_SESSION['errores_checkout'] = ['No se pudo registrar el detalle del pedido.'];
                header('Location: ' . url('pedido/checkout'));
                exit;
            }

            // Registrar uso del cupón si se aplicó alguno
            if ($cupon_id) {
                CuponHelper::registrarUso($cupon_id, $cliente_id, $pedido_id);
                CuponHelper::limpiarCuponSesion(); // limpiar sesión del cupón
            }

            // Vaciar carrito y redirigir a confirmación
            $_SESSION['carrito'] = [];
            header('Location: ' . url('pedido/confirmacion/' . $pedido_id));
            exit;
        }
    }

    // Muestra un pedido específico
    public function ver($id)
    {
        $pedido = $this->pedidoModel->obtenerPorId($id);
        $detalles = $this->detalleModel->obtenerPorPedido($id);
        require __DIR__ . '/../views/pedido/ver.php';
    }

    // Lista todos los pedidos
    public function listar()
    {
        $pedidos = $this->pedidoModel->obtenerTodos();
        require __DIR__ . '/../views/pedido/listar.php';
    }

    // Aplicar cupón via AJAX
    public function aplicarCupon()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $carrito = $_SESSION['carrito'] ?? [];
        
        if (empty($codigo)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Código de cupón requerido']);
            exit;
        }

        if (empty($carrito)) {
            echo json_encode(['exito' => false, 'mensaje' => 'El carrito está vacío']);
            exit;
        }

        // Por ahora usamos cliente_id = 1 como ejemplo
        // En un sistema real, esto vendría del login del cliente
        $cliente_id = $_SESSION['cliente_id'] ?? 1;
        
        $resultado = CuponHelper::aplicarCupon($codigo, $cliente_id, $carrito);
        
        if ($resultado['exito']) {
            $_SESSION['cupon_aplicado'] = $resultado['cupon'];
        }
        
        echo json_encode($resultado);
        exit;
    }

    // Quitar cupón aplicado
    public function quitarCupon()
    {
        CuponHelper::limpiarCuponSesion();
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['exito' => true, 'mensaje' => 'Cupón removido']);
        } else {
            header('Location: ' . url('carrito/ver'));
        }
        exit;
    }

    // Cambia el estado del pedido
    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            if ($id && $estado) {
                $this->pedidoModel->actualizarEstado($id, $estado);
            }
            header('Location: ' . url('pedido/listar'));
            exit;
        }
    }

    // Muestra mensaje de confirmación de compra
    public function confirmacion($id = null)
    {
        if (!$id) {
            echo "ID de pedido no especificado.";
            return;
        }
        $pedido = $this->pedidoModel->obtenerPorId($id);
        require __DIR__ . '/../views/pedido/confirmacion.php';
    }

    // Guarda la observación del administrador
    public function guardarObservacion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $observacion = trim($_POST['observacion'] ?? '');
            if ($id) {
                $this->pedidoModel->actualizarObservacionesAdmin($id, $observacion);
            }
            header('Location: ' . url('pedido/listar'));
            exit;
        }
    }
}
