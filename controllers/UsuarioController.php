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
}
