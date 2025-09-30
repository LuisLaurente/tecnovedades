<?php

namespace Controllers;

use Models\Usuario;
use Models\Rol;
use Core\Helpers\Validator;
use Core\Helpers\Sanitizer;

class UsuarioController extends BaseController
{
    private $usuarioModel;
    private $rolModel;

    public function __construct()
    {
        // Verificar autenticación y permisos
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
    }

    /**
     * Mostrar lista de usuarios
     */
    public function index()
    {
        try {
            $usuarios = $this->usuarioModel->obtenerTodos();
            $estadisticas = $this->usuarioModel->obtenerEstadisticas();

            // Procesar mensajes de estado
            $success = $_GET['success'] ?? '';
            $error = $_GET['error'] ?? '';

            require_once __DIR__ . '/../views/usuario/index.php';
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::index: " . $e->getMessage());
            header('Location: ' . url('/error'));
            exit;
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear()
    {
        try {
            $roles = $this->rolModel->obtenerActivos();
            require_once __DIR__ . '/../views/usuario/crear.php';
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::crear: " . $e->getMessage());
            header('Location: ' . url('/error'));
            exit;
        }
    }

    /**
     * Procesar creación de usuario
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('/usuario'));
                exit;
            }

            // Sanitizar datos
            $datos = [
                'nombre' => Sanitizer::cleanString($_POST['nombre'] ?? ''),
                'email' => Sanitizer::sanitizeEmail($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirmar_password' => $_POST['confirmar_password'] ?? '',
                'rol_id' => (int)($_POST['rol'] ?? 2), // Por defecto rol 'usuario'
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];

            // Validar datos
            $errores = $this->validarDatos($datos);

            if (!empty($errores)) {
                $error = urlencode(implode(', ', $errores));
                header('Location: ' . url("/usuario/crear?error=$error"));
                exit;
            }

            // Verificar si el email ya existe
            if ($this->usuarioModel->existeEmail($datos['email'])) {
                $error = urlencode('El email ya está registrado');
                header('Location: ' . url("/usuario/crear?error=$error"));
                exit;
            }

            // Crear usuario
            $resultado = $this->usuarioModel->crear($datos);

            if ($resultado) {
                $success = urlencode('Usuario creado exitosamente');
                header('Location: ' . url("/usuario?success=$success"));
                exit;
            } else {
                $error = urlencode('Error al crear el usuario');
                header('Location: ' . url("/usuario/crear?error=$error"));
                exit;
            }
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::store: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/usuario/crear?error=$error"));
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id)
    {
        try {
            $usuario = $this->usuarioModel->obtenerPorId($id);

            if (!$usuario) {
                $error = urlencode('Usuario no encontrado');
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }

            $roles = $this->rolModel->obtenerActivos();

            require_once __DIR__ . '/../views/usuario/editar.php';
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::editar: " . $e->getMessage());
            header('Location: ' . url('/error'));
            exit;
        }
    }

    /**
     * Procesar actualización de usuario
     */
    public function actualizar($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('/usuario'));
                exit;
            }

            // Verificar que el usuario existe
            $usuarioExistente = $this->usuarioModel->obtenerPorId($id);
            if (!$usuarioExistente) {
                $error = urlencode('Usuario no encontrado');
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }

            // Sanitizar datos
            $datos = [
                'nombre' => Sanitizer::cleanString($_POST['nombre'] ?? ''),
                'email' => Sanitizer::sanitizeEmail($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirmar_password' => $_POST['confirmar_password'] ?? '',
                'rol_id' => (int)($_POST['rol'] ?? 2),
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];

            // Validar datos (para actualización)
            $errores = $this->validarDatos($datos, false, $id);

            if (!empty($errores)) {
                $error = urlencode(implode(', ', $errores));
                header('Location: ' . url("/usuario/editar/$id?error=$error"));
                exit;
            }

            // Verificar si el email ya existe (excluyendo el usuario actual)
            if ($this->usuarioModel->existeEmail($datos['email'], $id)) {
                $error = urlencode('El email ya está registrado por otro usuario');
                header('Location: ' . url("/usuario/editar/$id?error=$error"));
                exit;
            }

            // Actualizar usuario
            $resultado = $this->usuarioModel->actualizar($id, $datos);

            if ($resultado) {
                $success = urlencode('Usuario actualizado exitosamente');
                header('Location: ' . url("/usuario?success=$success"));
                exit;
            } else {
                $error = urlencode('Error al actualizar el usuario');
                header('Location: ' . url("/usuario/editar/$id?error=$error"));
                exit;
            }
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::actualizar: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/usuario/editar/$id?error=$error"));
            exit;
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('/usuario'));
                exit;
            }

            // Verificar que el usuario existe
            $usuario = $this->usuarioModel->obtenerPorId($id);
            if (!$usuario) {
                $error = urlencode('Usuario no encontrado');
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }

            // Eliminar usuario
            $resultado = $this->usuarioModel->eliminar($id);

            if ($resultado) {
                $success = urlencode('Usuario eliminado exitosamente');
                header('Location: ' . url("/usuario?success=$success"));
                exit;
            } else {
                $error = urlencode('Error al eliminar el usuario');
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::eliminar: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/usuario?error=$error"));
            exit;
        }
    }

    /**
     * Cambiar estado activo del usuario
     */
    public function cambiarEstado($id)
    {
        try {
            error_log("UsuarioController::cambiarEstado llamado con ID: " . $id);
            error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("Método no es POST, redirigiendo");
                header('Location: ' . url('/usuario'));
                exit;
            }

            // Verificar que el usuario existe
            $usuario = $this->usuarioModel->obtenerPorId($id);
            error_log("Usuario obtenido: " . ($usuario ? json_encode($usuario) : 'null'));

            if (!$usuario) {
                $error = urlencode('Usuario no encontrado');
                error_log("Usuario no encontrado con ID: " . $id);
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }

            // Cambiar estado
            $nuevoEstado = $usuario['activo'] ? 0 : 1;
            error_log("Estado actual: " . $usuario['activo'] . ", nuevo estado: " . $nuevoEstado);

            $resultado = $this->usuarioModel->cambiarEstado($id, $nuevoEstado);
            error_log("Resultado del cambio: " . ($resultado ? 'true' : 'false'));

            if ($resultado) {
                $estado = $nuevoEstado ? 'activado' : 'desactivado';
                $success = urlencode("Usuario $estado exitosamente");
                header('Location: ' . url("/usuario?success=$success"));
                exit;
            } else {
                $error = urlencode('Error al cambiar el estado del usuario');
                header('Location: ' . url("/usuario?error=$error"));
                exit;
            }
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::cambiarEstado: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/usuario?error=$error"));
            exit;
        }
    }

    /**
     * Validar datos de usuario
     */
    private function validarDatos($datos, $esCreacion = true, $idUsuario = null)
    {
        $errores = [];

        // Validar nombre
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        } elseif (strlen($datos['nombre']) < 2) {
            $errores[] = 'El nombre debe tener al menos 2 caracteres';
        }

        // Validar email
        if (empty($datos['email'])) {
            $errores[] = 'El email es requerido';
        } elseif (!Validator::isEmail($datos['email'])) {
            $errores[] = 'El email no tiene un formato válido';
        }

        // Validar password (solo en creación o si se proporciona en edición)
        if ($esCreacion || !empty($datos['password'])) {
            if (empty($datos['password'])) {
                $errores[] = 'La contraseña es requerida';
            } elseif (strlen($datos['password']) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif ($datos['password'] !== $datos['confirmar_password']) {
                $errores[] = 'Las contraseñas no coinciden';
            }
        }

        // Validar rol_id
        if (empty($datos['rol_id']) || !is_numeric($datos['rol_id'])) {
            $errores[] = 'Debe seleccionar un rol válido';
        } else {
            // Verificar que el rol existe y está activo
            $rol = $this->rolModel->obtenerPorId($datos['rol_id']);
            if (!$rol) {
                $errores[] = 'El rol seleccionado no existe';
            } elseif (!$rol['activo']) {
                $errores[] = 'El rol seleccionado no está activo';
            }
        }

        return $errores;
    }

    /**
     * Ver pedidos del usuario actual
     */
    public function pedidos()
    {
        if (!\Core\Helpers\SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }

        // Verificación adicional de seguridad
        $userRole = \Core\Helpers\SessionHelper::getRole();
        $userPermissions = \Core\Helpers\SessionHelper::getPermissions();

        // Solo permitir acceso a admins o usuarios con rol 'usuario'
        $isAdmin = in_array('usuarios', $userPermissions ?: []);
        $isCliente = false;

        if (is_array($userRole) && isset($userRole['nombre'])) {
            $isCliente = ($userRole['nombre'] === 'usuario');
        } elseif (is_string($userRole)) {
            $isCliente = ($userRole === 'usuario');
        } else {
            // Verificar por permisos - clientes típicamente solo tienen 'perfil'
            $isCliente = in_array('perfil', $userPermissions ?: []) &&
                !in_array('productos', $userPermissions ?: []);
        }

        if (!$isAdmin && !$isCliente) {
            error_log("❌ Acceso denegado a pedidos: usuario no es admin ni cliente");
            header('Location: ' . url('/error/forbidden'));
            exit;
        }

        try {
            $usuario = \Core\Helpers\SessionHelper::getUser();

            // Obtener pedidos del usuario
            $pedidoModel = new \Models\Pedido();
            $pedidos = [];

            if ($isAdmin) {
                // Los admins pueden ver todos los pedidos (opcional: cambiar esta lógica si solo quieres que vean los suyos)
                try {
                    $pedidos = $pedidoModel->obtenerTodosConDirecciones();
                } catch (\Exception $e) {
                    $db = \Core\Database::getConexion();
                    $stmt = $db->query("SELECT * FROM pedidos ORDER BY creado_en DESC");
                    $pedidos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            } else {
                // Los clientes solo pueden ver sus propios pedidos
                try {
                    $todosLosPedidos = $pedidoModel->obtenerTodosConDirecciones();
                    // Filtrar solo los pedidos del usuario actual
                    foreach ($todosLosPedidos as $pedido) {
                        if ($pedido['cliente_id'] == $usuario['id']) {
                            $pedidos[] = $pedido;
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback: obtener pedidos básicos
                    $db = \Core\Database::getConexion();
                    $stmt = $db->prepare("SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY creado_en DESC");
                    $stmt->execute([$usuario['id']]);
                    $pedidos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            }

            // Obtener detalles de cada pedido
            $detalleModel = new \Models\DetallePedido();
            $pedidoDireccionModel = new \Models\PedidoDireccion();

            foreach ($pedidos as &$pedido) {
                // Obtener detalles del pedido con información de productos
                try {
                    $pedido['detalles'] = $detalleModel->obtenerPorPedido($pedido['id']);
                } catch (\Exception $e) {
                    // Fallback al método original si hay error
                    try {
                        $pedido['detalles'] = $detalleModel->obtenerPorPedido($pedido['id']);
                    } catch (\Exception $e2) {
                        $pedido['detalles'] = [];
                    }
                }

                // Calcular total si no está presente o es 0
                if (!isset($pedido['total']) || $pedido['total'] == 0) {
                    $total = 0;
                    if (isset($pedido['detalles']) && is_array($pedido['detalles'])) {
                        foreach ($pedido['detalles'] as $detalle) {
                            $precio = floatval($detalle['precio_unitario'] ?? 0);
                            $cantidad = intval($detalle['cantidad'] ?? 0);
                            $total += $precio * $cantidad;
                        }
                    }
                    $pedido['total'] = $total;
                }

                // Obtener dirección del pedido
                try {
                    $pedido['direccion_envio'] = $pedidoDireccionModel->obtenerDireccionCompleta($pedido['id']);
                } catch (\Exception $e) {
                    $pedido['direccion_envio'] = 'Dirección no disponible';
                }
            }

            require_once __DIR__ . '/../views/usuario/pedidos.php';
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::pedidos: " . $e->getMessage());
            header('Location: ' . url('/error'));
            exit;
        }
    }

    /**
     * Obtener detalles de un pedido específico (AJAX)
     */
    public function detallePedido($pedidoId = null)
    {
        // Asegurar que sea una petición AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso no autorizado']);
            exit;
        }

        if (!\Core\Helpers\SessionHelper::isAuthenticated()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        if (!$pedidoId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de pedido requerido']);
            exit;
        }

        try {
            $usuario = \Core\Helpers\SessionHelper::getUser();
            $userRole = \Core\Helpers\SessionHelper::getRole();
            $userPermissions = \Core\Helpers\SessionHelper::getPermissions();

            // Verificar permisos
            $isAdmin = in_array('usuarios', $userPermissions ?: []);
            $isCliente = false;

            if (is_array($userRole) && isset($userRole['nombre'])) {
                $isCliente = ($userRole['nombre'] === 'usuario');
            } elseif (is_string($userRole)) {
                $isCliente = ($userRole === 'usuario');
            } else {
                $isCliente = in_array('perfil', $userPermissions ?: []) &&
                    !in_array('productos', $userPermissions ?: []);
            }

            if (!$isAdmin && !$isCliente) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Sin permisos']);
                exit;
            }

            // Obtener el pedido
            $db = \Core\Database::getConexion();

            if ($isAdmin) {
                // Admin puede ver cualquier pedido
                $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ?");
                $stmt->execute([$pedidoId]);
            } else {
                // Cliente solo puede ver sus propios pedidos
                $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND cliente_id = ?");
                $stmt->execute([$pedidoId, $usuario['id']]);
            }

            $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$pedido) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Pedido no encontrado']);
                exit;
            }

            // Obtener detalles del pedido
            $detalleModel = new \Models\DetallePedido();
            $pedidoDireccionModel = new \Models\PedidoDireccion();

            try {
                $pedido['detalles'] = $detalleModel->obtenerPorPedido($pedido['id']);
            } catch (\Exception $e) {
                $pedido['detalles'] = [];
            }

            // Calcular total si no está presente o es 0
            if (!isset($pedido['total']) || $pedido['total'] == 0) {
                $total = 0;
                if (isset($pedido['detalles']) && is_array($pedido['detalles'])) {
                    foreach ($pedido['detalles'] as $detalle) {
                        $precio = floatval($detalle['precio_unitario'] ?? 0);
                        $cantidad = intval($detalle['cantidad'] ?? 0);
                        $total += $precio * $cantidad;
                    }
                }
                $pedido['total'] = $total;
            }

            try {
                $pedido['direccion_envio'] = $pedidoDireccionModel->obtenerDireccionCompleta($pedido['id']);
            } catch (\Exception $e) {
                $pedido['direccion_envio'] = 'Dirección no disponible';
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'pedido' => $pedido
            ]);
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::detallePedido: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
}
