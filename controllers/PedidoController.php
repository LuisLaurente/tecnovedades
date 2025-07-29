<?php

namespace Controllers;

use Models\Pedido;
use Models\Cliente;
use Models\DetallePedido;
use Core\Helpers\PromocionHelper;
use Models\Cupon;

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

            // Crear cliente
            $cliente_id = $this->clienteModel->crear($nombre, $direccion, $telefono, $correo);
            if (!$cliente_id) {
                $_SESSION['errores_checkout'] = ['No se pudo registrar el cliente.'];
                header('Location: ' . url('pedido/checkout'));
                exit;
            }

            // Calcular subtotal y aplicar promociones
            $usuario = $_SESSION['usuario'] ?? null;
            $promociones = PromocionHelper::evaluar($carrito, $usuario);
            $totales = PromocionHelper::calcularTotales($carrito, $promociones);

            // Aplicar cupón si existe
            $cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
            if ($cupon_aplicado) {
                if ($cupon_aplicado['tipo'] === 'descuento_porcentaje') {
                    $totales['descuento'] += $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
                } elseif ($cupon_aplicado['tipo'] === 'descuento_fijo') {
                    $totales['descuento'] += $cupon_aplicado['valor'];
                } elseif ($cupon_aplicado['tipo'] === 'envio_gratis') {
                    $totales['envio_gratis'] = true;
                }
                $totales['total'] = max($totales['subtotal'] - $totales['descuento'], 0);
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

            // Registrar uso del cupón (si existe)
            if ($cupon_aplicado) {
                $cuponModel = new Cupon();
                $cuponModel->registrarUso($cupon_aplicado['id'], $usuario['id'] ?? null, $pedido_id);
                unset($_SESSION['cupon_aplicado']); // limpiar sesión del cupón
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
