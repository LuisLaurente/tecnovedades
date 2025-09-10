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
            
// ==================== DATOS DE ENVÍO ====================
$envio_nombre = trim($_POST['nombre'] ?? '');
$envio_celular = trim($_POST['telefono'] ?? '');
$envio_ubicacion = trim($_POST['ubicacion'] ?? '');
$envio_direccion = trim($_POST['direccion'] ?? '');
$envio_distrito = trim($_POST['distrito'] ?? '');
$envio_provincia = trim($_POST['provincia'] ?? '');
$envio_departamento = trim($_POST['departamento'] ?? '');
$envio_referencia = trim($_POST['referencia'] ?? '');
            
            // ==================== DATOS DE FACTURACIÓN ====================
            $facturacion_tipo_documento = trim($_POST['facturacion_tipo_documento'] ?? '');
            $facturacion_numero_documento = trim($_POST['facturacion_numero_documento'] ?? '');
            $facturacion_nombre = trim($_POST['facturacion_nombre'] ?? '');
            $facturacion_direccion = trim($_POST['facturacion_direccion'] ?? '');
            $facturacion_email = trim($_POST['facturacion_email'] ?? '');
            
            // ==================== DATOS EXISTENTES ====================
            $direccion_id = $_POST['direccion_id'] ?? '';
            $guardar_direccion = isset($_POST['guardar_direccion']);
            $tipo_direccion = $_POST['tipo_direccion'] ?? 'casa';
            $nombre_direccion = trim($_POST['nombre_direccion'] ?? '');
            
            $carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
            $errores = [];

            // ==================== VALIDACIONES ====================
            // Validaciones de envío
            if ($envio_nombre === '') $errores[] = 'El nombre de envío es obligatorio.';
            if ($envio_celular === '') $errores[] = 'El celular de envío es obligatorio.';
            if ($envio_ubicacion === '') $errores[] = 'La ubicación de envío es obligatoria.';
            if ($envio_direccion === '') $errores[] = 'La dirección de envío es obligatoria.';
            
            // Validaciones de facturación
            if ($facturacion_tipo_documento === '') $errores[] = 'El tipo de documento es obligatorio.';
            if ($facturacion_numero_documento === '') $errores[] = 'El número de documento es obligatorio.';
            if ($facturacion_nombre === '') $errores[] = 'El nombre o razón social es obligatorio.';
            if ($facturacion_direccion === '') $errores[] = 'La dirección fiscal es obligatoria.';
            if ($facturacion_email === '') $errores[] = 'El correo electrónico es obligatorio.';
            
            if (!filter_var($facturacion_email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del correo electrónico no es válido.';
            }
            
            if (!preg_match('/^[0-9\s\+\-\(\)]+$/', $envio_celular)) {
                $errores[] = 'El celular solo debe contener números, espacios y símbolos válidos.';
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

                // ==================== CALCULAR COSTO DE ENVÍO ====================
                $costo_envio = 0;
                if ($envio_ubicacion === 'lima') {
                    $costo_envio = 8.00;
                } elseif ($envio_ubicacion === 'provincia') {
                    $costo_envio = 12.00;
                }

                // ==================== ACTUALIZAR TELÉFONO DEL USUARIO ====================
                $telefonoActual = null;
                try {
                    $stmt = $conexion->prepare("SELECT telefono FROM usuario_detalles WHERE usuario_id = ?");
                    $stmt->execute([$usuario['id']]);
                    $detalleUsuario = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $telefonoActual = $detalleUsuario['telefono'] ?? null;
                } catch (Exception $e) {
                    // Si la tabla usuario_detalles no existe, continuar
                }
                
                if ($envio_celular !== $telefonoActual) {
                    try {
                        $stmt = $conexion->prepare("UPDATE usuario_detalles SET telefono = ? WHERE usuario_id = ?");
                        $stmt->execute([$envio_celular, $usuario['id']]);
                        
                        if ($stmt->rowCount() === 0) {
                            $stmt = $conexion->prepare("INSERT INTO usuario_detalles (usuario_id, telefono) VALUES (?, ?)");
                            $stmt->execute([$usuario['id'], $envio_celular]);
                        }
                    } catch (Exception $e) {
                        error_log("No se pudo actualizar teléfono en usuario_detalles: " . $e->getMessage());
                    }
                }

                // ==================== MANEJAR DIRECCIÓN DE ENVÍO ====================
                $direccion_completa = $envio_direccion;
                if ($envio_distrito || $envio_provincia || $envio_departamento) {
                    $ubicacion = [];
                    if ($envio_distrito) $ubicacion[] = $envio_distrito;
                    if ($envio_provincia) $ubicacion[] = $envio_provincia;
                    if ($envio_departamento) $ubicacion[] = $envio_departamento;
                    $direccion_completa .= ', ' . implode(', ', $ubicacion);
                }
                if ($envio_referencia) {
                    $direccion_completa .= ' - ' . $envio_referencia;
                }

                // Determinar qué dirección usar
                $direccion_id_para_pedido = null;
                $direccion_temporal = null;

                if (!empty($direccion_id)) {
                    $direccion_id_para_pedido = $direccion_id;
                } else {
                    $direccion_temporal = $direccion_completa;
                }

                // Guardar nueva dirección si se solicitó
                if ($guardar_direccion && empty($direccion_id)) {
                    try {
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
                            $envio_direccion,
                            $envio_distrito,
                            $envio_provincia,
                            $envio_departamento,
                            $envio_referencia,
                            $es_primera ? 1 : 0
                        ]);
                        
                        $direccion_id_para_pedido = $conexion->lastInsertId();
                        $direccion_temporal = null;
                    } catch (Exception $e) {
                        error_log("No se pudo guardar la dirección: " . $e->getMessage());
                    }
                }

                // ==================== CALCULAR TOTALES CON PROMOCIONES ====================
                $promociones = PromocionHelper::evaluar($carrito, $usuario);
                $totales = PromocionHelper::calcularTotales($carrito, $promociones);
                
                // Agregar costo de envío al total
                $totales['costo_envio'] = $costo_envio;
                $totales['total'] += $costo_envio;

                // ==================== APLICAR CUPÓN ====================
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
                        $totales['total'] = max($totales['subtotal'] - $totales['descuento'] + $costo_envio, 0);
                    }
                }

                // ==================== PREPARAR DATOS DEL PEDIDO ====================
                $pedido_data = [
                    'cupon_id' => $cupon_id,
                    'cupon_codigo' => $cupon_codigo,
                    'descuento_cupon' => $descuento_cupon,
                    'subtotal' => $totales['subtotal'],
                    'descuento_promocion' => $totales['descuento'] - $descuento_cupon,
                    'costo_envio' => $costo_envio,
                    // Datos de facturación
                    'facturacion_tipo_documento' => $facturacion_tipo_documento,
                    'facturacion_numero_documento' => $facturacion_numero_documento,
                    'facturacion_nombre' => $facturacion_nombre,
                    'facturacion_direccion' => $facturacion_direccion,
                    'facturacion_email' => $facturacion_email
                ];

                // ==================== CREAR PEDIDO ====================
                $pedido_id = $this->pedidoModel->crear($usuario['id'], $totales['total'], 'pendiente', $pedido_data);
                if (!$pedido_id) {
                    throw new Exception('No se pudo crear el pedido');
                }

                // ==================== GUARDAR DIRECCIÓN DEL PEDIDO ====================
                try {
                    $this->pedidoDireccionModel->crear($pedido_id, $direccion_id_para_pedido, $direccion_temporal);
                } catch (Exception $e) {
                    error_log("No se pudo guardar la dirección del pedido: " . $e->getMessage());
                }

                // ==================== GUARDAR DETALLE DEL PEDIDO ====================
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

                // ==================== REGISTRAR USO DEL CUPÓN ====================
                if ($cupon_id) {
                    CuponHelper::registrarUso($cupon_id, $usuario['id'], $pedido_id);
                    unset($_SESSION['cupon_aplicado']);
                }

                $conexion->commit();

                // ==================== LIMPIAR Y REDIRIGIR ====================
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

    // ==================== MÉTODOS EXISTENTES (sin cambios) ====================
    
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
    // En PedidoController.php o en un nuevo DireccionController.php
public function eliminarDireccion()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $direccionId = $_POST['id'] ?? null;
        
        if (!$direccionId) {
            echo json_encode(['success' => false, 'message' => 'ID de dirección no proporcionado']);
            exit;
        }
        
        try {
            $conexion = \Core\Database::getConexion();
            
            // Verificar que la dirección pertenece al usuario
            $usuario = $_SESSION['usuario'];
            $stmt = $conexion->prepare("SELECT usuario_id FROM direcciones WHERE id = ?");
            $stmt->execute([$direccionId]);
            $direccion = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$direccion || $direccion['usuario_id'] != $usuario['id']) {
                echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar esta dirección']);
                exit;
            }
            
            // Eliminar la dirección
            $stmt = $conexion->prepare("UPDATE direcciones SET activa = 0 WHERE id = ?");
            $stmt->execute([$direccionId]);
            
            echo json_encode(['success' => true, 'message' => 'Dirección eliminada correctamente']);
            
        } catch (Exception $e) {
            error_log("Error al eliminar dirección: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la dirección']);
        }
    }
    exit;
}
}