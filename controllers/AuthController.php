<?php

namespace Controllers;

use Models\Usuario;
use Models\Rol;
use Core\Helpers\SessionHelper;
use Core\Helpers\Validator;
use Core\Helpers\CsrfHelper;
use Core\Helpers\LoginRateHelper;
use Core\Helpers\SecurityLogger;

class AuthController extends BaseController
{
    private $usuarioModel;
    private $rolModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        // Si ya está autenticado, redirigir al perfil
        if (SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/profile'));
            exit;
        }

        // Limpiar intentos antiguos para mantener la sesión ligera
        LoginRateHelper::cleanOldAttempts();
        
        $error = $_GET['error'] ?? '';
        
        // Verificar si hay una IP bloqueada
        $ip = $_SERVER['REMOTE_ADDR'];
        $blockInfo = LoginRateHelper::isBlocked($ip);
        if ($blockInfo) {
            $error = $blockInfo['message'];
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function authenticate()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('/auth/login'));
                exit;
            }
            
            // Verificar token CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (empty($csrfToken) || !CsrfHelper::validateToken($csrfToken, 'login_form')) {
                SecurityLogger::log(SecurityLogger::CSRF_ERROR, 'Token CSRF inválido en intento de login', [
                    'email' => $email ?? 'no proporcionado'
                ]);
                $error = urlencode('Error de seguridad: Token inválido o expirado. Por favor, intente nuevamente.');
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            // Verificar bloqueo por intentos fallidos (usando IP si el email no existe)
            $identifier = $email ?: $_SERVER['REMOTE_ADDR']; 
            $blockInfo = LoginRateHelper::isBlocked($identifier);
            
            if ($blockInfo) {
                SecurityLogger::log(SecurityLogger::ACCOUNT_LOCKED, 'Intento de acceso a cuenta bloqueada', [
                    'email' => $email,
                    'remaining_time' => $blockInfo['remaining_seconds']
                ]);
                $error = urlencode($blockInfo['message']);
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            // Validar datos
            $errores = [];
            if (empty($email)) {
                $errores[] = 'El email es requerido';
            } elseif (!Validator::email($email)) {
                $errores[] = 'El email no es válido';
            }

            if (empty($password)) {
                $errores[] = 'La contraseña es requerida';
            }

            if (!empty($errores)) {
                $error = urlencode(implode(', ', $errores));
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            // Buscar usuario por email
            $usuario = $this->usuarioModel->obtenerPorEmail($email);
            
            if (!$usuario) {
                // Registrar intento fallido
                $attempts = LoginRateHelper::recordFailedAttempt($identifier);
                
                SecurityLogger::log(SecurityLogger::LOGIN_FAIL, 'Intento de login con email inexistente', [
                    'email' => $email,
                    'attempt_count' => $attempts['count']
                ]);
                
                $error = urlencode('Credenciales incorrectas');
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            // Verificar si el usuario está activo
            if (!$usuario['activo']) {
                SecurityLogger::log(SecurityLogger::LOGIN_FAIL, 'Intento de login con cuenta desactivada', [
                    'email' => $email,
                    'user_id' => $usuario['id']
                ]);
                
                $error = urlencode('Tu cuenta está desactivada. Contacta al administrador.');
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            // Verificar contraseña
            if (!password_verify($password, $usuario['password'])) {
                // Registrar intento fallido
                $attempts = LoginRateHelper::recordFailedAttempt($email);
                
                SecurityLogger::log(SecurityLogger::LOGIN_FAIL, 'Contraseña incorrecta', [
                    'email' => $email,
                    'user_id' => $usuario['id'],
                    'attempt_count' => $attempts['count']
                ]);
                
                $error = urlencode('Credenciales incorrectas');
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }
            
            // Éxito: resetear intentos fallidos
            LoginRateHelper::resetAttempts($email);

            // Obtener información del rol
            $rol = $this->rolModel->obtenerPorId($usuario['rol_id']);
            if (!$rol || !$rol['activo']) {
                $error = urlencode('Tu rol está desactivado. Contacta al administrador.');
                header('Location: ' . url("/auth/login?error=$error"));
                exit;
            }

            // Crear sesión
            SessionHelper::login($usuario, $rol);
            //Mostrar popup en esta nueva sesión
            $_SESSION['mostrar_popup'] = true;
            
            // Registrar login exitoso
            SecurityLogger::log(SecurityLogger::LOGIN_SUCCESS, 'Login exitoso', [
                'user_id' => $usuario['id'],
                'email' => $usuario['email'],
                'rol' => $rol['nombre'],
                'remember_me' => $remember ? 'sí' : 'no'
            ]);
            // Si marcó "recordarme", crear cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                // Aquí podrías guardar el token en la base de datos para "remember me"
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 días
            }

            // Redirigir al perfil
            header('Location: ' . url('/auth/profile'));
            exit;

        } catch (\Exception $e) {
            error_log("Error en AuthController::authenticate: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/auth/login?error=$error"));
            exit;
        }
    }

    /**
     * Dashboard principal - redirige al perfil
     */
    public function dashboard()
    {
        // Redirigir al perfil que es la nueva página principal
        header('Location: ' . url('/auth/profile'));
        exit;
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        // Registrar logout antes de destruir la sesión para tener la información del usuario
        if (SessionHelper::isAuthenticated()) {
            $usuario = SessionHelper::getUser();
            SecurityLogger::log(SecurityLogger::LOGOUT, 'Cierre de sesión', [
                'user_id' => $usuario['id'] ?? 'desconocido',
                'email' => $usuario['email'] ?? 'desconocido'
            ]);
        }
        
        SessionHelper::logout();
        
        // Eliminar cookie de "recordarme" si existe
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        header('Location: ' . url('/'));
        exit;
    }

    /**
     * Perfil del usuario
     */
    public function profile()
    {
        if (!SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }

        $usuario = SessionHelper::getUser();
        $rol = SessionHelper::getRole();

        require_once __DIR__ . '/../views/auth/profile.php';
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile()
    {
        if (!SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/auth/profile'));
            exit;
        }

        try {
            $usuario = SessionHelper::getUser();
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];

            // Validar datos
            $errores = [];
            if (empty($datos['nombre'])) {
                $errores[] = 'El nombre es requerido';
            }
            
            if (empty($datos['email'])) {
                $errores[] = 'El email es requerido';
            } elseif (!Validator::email($datos['email'])) {
                $errores[] = 'El email no es válido';
            }

            // Verificar si el email ya existe (excluyendo el usuario actual)
            if ($this->usuarioModel->existeEmail($datos['email'], $usuario['id'])) {
                $errores[] = 'El email ya está en uso';
            }

            if (!empty($errores)) {
                $error = urlencode(implode(', ', $errores));
                header('Location: ' . url("/auth/profile?error=$error"));
                exit;
            }

            // Actualizar usuario
            $resultado = $this->usuarioModel->actualizar($usuario['id'], $datos);
            
            if ($resultado) {
                // Actualizar datos en la sesión
                $usuarioActualizado = $this->usuarioModel->obtenerPorId($usuario['id']);
                SessionHelper::updateUser($usuarioActualizado);
                
                $success = urlencode('Perfil actualizado exitosamente');
                header('Location: ' . url("/auth/profile?success=$success"));
                exit;
            } else {
                $error = urlencode('Error al actualizar el perfil');
                header('Location: ' . url("/auth/profile?error=$error"));
                exit;
            }

        } catch (\Exception $e) {
            error_log("Error en AuthController::updateProfile: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            header('Location: ' . url("/auth/profile?error=$error"));
            exit;
        }
    }
}
