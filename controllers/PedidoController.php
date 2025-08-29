<?php

namespace Controllers;

use Models\Pedido;
use Models\Usuario;
use Models\DetallePedido;
use Models\PedidoDireccion;
use Core\Helpers\PromocionHelper;
use Core\Helpers\CuponHelper;
use Exception;

class PedidoController
{
    private $pedidoModel;
    private $usuarioModel;
    private $detalleModel;
    private $pedidoDireccionModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->usuarioModel = new Usuario();
        $this->detalleModel = new DetallePedido();
        $this->pedidoDireccionModel = new PedidoDireccion();
    }

    // Página de pre-checkout para usuarios no autenticados
    public function precheckout()
    {
        // Si ya está logueado, redirigir directo al checkout
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . url('pedido/checkout'));
            exit;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        if (empty($carrito)) {
            header('Location: ' . url('carrito/ver'));
            exit;
        }

        // Calcular totales para mostrar en la página
        $usuario = null; // Usuario no autenticado
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        require __DIR__ . '/../views/pedido/precheckout.php';
    }

    // Muestra formulario de checkout
    public function checkout()
    {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . url('pedido/precheckout'));
            exit;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        $usuario = $_SESSION['usuario'] ?? null;

        // Evaluar promociones nuevamente antes de procesar pedido
        $promociones = PromocionHelper::evaluar($carrito, $usuario);
        $totales = PromocionHelper::calcularTotales($carrito, $promociones);

        require __DIR__ . '/../views/pedido/checkout_nuevo.php';
    }

    // Procesa y guarda el pedido completo
    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que el usuario esté autenticado
            if (!isset($_SESSION['usuario'])) {
                header('Location: ' . url('pedido/precheckout'));
                exit;
            }

            $usuario = $_SESSION['usuario'];
            $nombre = trim($_POST['nombre'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $distrito = trim($_POST['distrito'] ?? '');
            $provincia = trim($_POST['provincia'] ?? '');
            $departamento = trim($_POST['departamento'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            $direccion_id = $_POST['direccion_id'] ?? '';
            $guardar_direccion = isset($_POST['guardar_direccion']);
            $tipo_direccion = $_POST['tipo_direccion'] ?? 'casa';
            $nombre_direccion = trim($_POST['nombre_direccion'] ?? '');
            
            $carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
            $errores = [];

            // Validaciones básicas
            if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
            if ($telefono === '') $errores[] = 'El teléfono es obligatorio.';
            if ($direccion === '') $errores[] = 'La dirección es obligatoria.';
            if (!preg_match('/^[0-9\s\+\-\(\)]+$/', $telefono)) {
                $errores[] = 'El teléfono solo debe contener números, espacios y símbolos válidos.';
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

            try {
                $conexion = \Core\Database::getConexion();
                $conexion->beginTransaction();

                // Actualizar teléfono del usuario si es necesario
                $telefonoActual = null;
                try {
                    // Obtener teléfono actual del usuario
                    $stmt = $conexion->prepare("SELECT telefono FROM usuario_detalles WHERE usuario_id = ?");
                    $stmt->execute([$usuario['id']]);
                    $detalleUsuario = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $telefonoActual = $detalleUsuario['telefono'] ?? null;
                } catch (Exception $e) {
                    // Si la tabla usuario_detalles no existe, continuar
                }
                
                if ($telefono !== $telefonoActual) {
                    try {
                        // Intentar actualizar en usuario_detalles (si existe)
                        $stmt = $conexion->prepare("UPDATE usuario_detalles SET telefono = ? WHERE usuario_id = ?");
                        $stmt->execute([$telefono, $usuario['id']]);
                        
                        if ($stmt->rowCount() === 0) {
                            // Si no existe el registro, crearlo
                            $stmt = $conexion->prepare("INSERT INTO usuario_detalles (usuario_id, telefono) VALUES (?, ?)");
                            $stmt->execute([$usuario['id'], $telefono]);
                        }
                    } catch (Exception $e) {
                        // Log del error pero continuar con el proceso
                        error_log("No se pudo actualizar teléfono en usuario_detalles: " . $e->getMessage());
                    }
                }

                // Manejar dirección de envío
                $direccion_completa = $direccion;
                if ($distrito || $provincia || $departamento) {
                    $ubicacion = [];
                    if ($distrito) $ubicacion[] = $distrito;
                    if ($provincia) $ubicacion[] = $provincia;
                    if ($departamento) $ubicacion[] = $departamento;
                    $direccion_completa .= ', ' . implode(', ', $ubicacion);
                }
                if ($referencia) {
                    $direccion_completa .= ' - ' . $referencia;
                }

                // Determinar qué dirección usar
                $direccion_id_para_pedido = null;
                $direccion_temporal = null;

                if (!empty($direccion_id)) {
                    // Se seleccionó una dirección existente
                    $direccion_id_para_pedido = $direccion_id;
                } else {
                    // Se ingresó una dirección nueva (temporal)
                    $direccion_temporal = $direccion_completa;
                }

                // Guardar nueva dirección si se solicitó y no se seleccionó una existente
                if ($guardar_direccion && empty($direccion_id)) {
                    try {
                        // Verificar si es la primera dirección (será principal)
                        $stmt = $conexion->prepare("SELECT COUNT(*) FROM direcciones WHERE usuario_id = ? AND activa = 1");
                        $stmt->execute([$usuario['id']]);
                        $es_primera = ($stmt->fetchColumn() == 0);

                        $stmt = $conexion->prepare("
                            INSERT INTO direcciones (usuario_id, tipo, nombre_direccion, direccion, distrito, provincia, departamento, referencia, es_principal, activa) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                        ");
                        $stmt->execute([
                            $usuario['id'],
                            $tipo_direccion,
                            $nombre_direccion ?: ucfirst($tipo_direccion),
                            $direccion,
                            $distrito,
                            $provincia,
                            $departamento,
                            $referencia,
                            $es_primera ? 1 : 0
                        ]);
                        
                        // Actualizar la dirección ID para el pedido
                        $direccion_id_para_pedido = $conexion->lastInsertId();
                        $direccion_temporal = null; // No necesitamos guardar como temporal
                    } catch (Exception $e) {
                        // Si las tablas no están migradas, usar dirección temporal
                        error_log("No se pudo guardar la dirección: " . $e->getMessage());
                    }
                }

                // Calcular totales con promociones
                $promociones = PromocionHelper::evaluar($carrito, $usuario);
                $totales = PromocionHelper::calcularTotales($carrito, $promociones);

                // Aplicar cupón si existe
                $cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
                $descuento_cupon = 0;
                $cupon_id = null;
                $cupon_codigo = null;
                
                if ($cupon_aplicado) {
                    $aplicacion = CuponHelper::aplicarCupon($cupon_aplicado['codigo'], $usuario['id'], $carrito);
                    if ($aplicacion['exito']) {
                        $descuento_cupon = $aplicacion['descuento'];
                        $cupon_id = $aplicacion['cupon']['id'];
                        $cupon_codigo = $aplicacion['cupon']['codigo'];
                        $totales['descuento'] += $descuento_cupon;
                        $totales['total'] = max($totales['subtotal'] - $totales['descuento'], 0);
                    }
                }

                // Preparar datos del cupón para el pedido
                $cupon_data = null;
                if ($cupon_id) {
                    $cupon_data = [
                        'cupon_id' => $cupon_id,
                        'cupon_codigo' => $cupon_codigo,
                        'descuento_cupon' => $descuento_cupon,
                        'subtotal' => $totales['subtotal'],
                        'descuento_promocion' => $totales['descuento'] - $descuento_cupon
                    ];
                }

                // Crear pedido con información de cupón
                $pedido_id = $this->pedidoModel->crear($usuario['id'], $totales['total'], 'pendiente', $cupon_data);
                if (!$pedido_id) {
                    throw new Exception('No se pudo crear el pedido');
                }

                // Guardar la dirección del pedido en la tabla pedido_direcciones
                try {
                    $this->pedidoDireccionModel->crear($pedido_id, $direccion_id_para_pedido, $direccion_temporal);
                } catch (Exception $e) {
                    // Si la tabla pedido_direcciones no existe aún, continuar
                    error_log("No se pudo guardar la dirección del pedido: " . $e->getMessage());
                }

                // Guardar detalle del pedido
                foreach ($carrito as $item) {
                    $ok = $this->detalleModel->crear(
                        $pedido_id,
                        $item['producto_id'],
                        $item['cantidad'],
                        $item['precio'],
                        $item['variante_id'] ?? null
                    );
                    if (!$ok) {
                        throw new Exception('No se pudo guardar el detalle del pedido');
                    }
                }

                // Registrar uso del cupón si se aplicó
                if ($cupon_id) {
                    CuponHelper::registrarUso($cupon_id, $usuario['id'], $pedido_id);
                    unset($_SESSION['cupon_aplicado']);
                }

                $conexion->commit();

                // Vaciar carrito y redirigir
                $_SESSION['carrito'] = [];
                unset($_SESSION['promociones']);
                
                header('Location: ' . url('pedido/confirmacion/' . $pedido_id));
                exit;

            } catch (Exception $e) {
                $conexion->rollback();
                error_log("Error en PedidoController::registrar: " . $e->getMessage());
                $_SESSION['errores_checkout'] = ['Error al procesar el pedido: ' . $e->getMessage()];
                header('Location: ' . url('pedido/checkout'));
                exit;
            }
        }
    }

    // Muestra un pedido específico
    public function ver($id)
    {
        $pedido = $this->pedidoModel->obtenerPorId($id);
        $detalles = $this->detalleModel->obtenerPorPedido($id);
        
        // Obtener dirección del pedido
        $direccion_pedido = null;
        try {
            $direccion_pedido = $this->pedidoDireccionModel->obtenerDireccionCompleta($id);
        } catch (Exception $e) {
            error_log("Error obteniendo dirección del pedido: " . $e->getMessage());
            $direccion_pedido = 'Dirección no disponible';
        }
        
        require __DIR__ . '/../views/pedido/ver.php';
    }

    // Lista todos los pedidos
    public function listar()
    {
        try {
            $pedidos = $this->pedidoModel->obtenerTodosConDirecciones();
        } catch (Exception $e) {
            // Fallback si hay error con direcciones
            $pedidos = $this->pedidoModel->obtenerTodos();
        }
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
        
        // Obtener dirección del pedido para mostrar en la confirmación
        $direccion_pedido = null;
        try {
            $direccion_pedido = $this->pedidoDireccionModel->obtenerDireccionCompleta($id);
        } catch (Exception $e) {
            error_log("Error obteniendo dirección del pedido: " . $e->getMessage());
            $direccion_pedido = 'Dirección no disponible';
        }
        
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
