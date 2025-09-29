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
        // Si ya está autenticado, redirigir al perfil o a la URL de redirección
        if (SessionHelper::isAuthenticated()) {
            $redirect = $_GET['redirect'] ?? '';
            if (!empty($redirect)) {
                header('Location: ' . url($redirect));
            } else {
                header('Location: ' . url('/auth/profile'));
            }
            exit;
        }

        // Limpiar intentos antiguos para mantener la sesión ligera
        LoginRateHelper::cleanOldAttempts();
        
        $error = $_GET['error'] ?? '';
        $redirect = $_GET['redirect'] ?? '';
        
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
            $redirect = $_POST['redirect'] ?? '';
            
            if (empty($csrfToken) || !CsrfHelper::validateToken($csrfToken, 'login_form')) {
                SecurityLogger::log(SecurityLogger::CSRF_ERROR, 'Token CSRF inválido en intento de login', [
                    'email' => $email ?? 'no proporcionado'
                ]);
                
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = 'Error de seguridad: Token inválido o expirado. Por favor, intente nuevamente.';
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode('Error de seguridad: Token inválido o expirado. Por favor, intente nuevamente.');
                    header('Location: ' . url("/auth/login?error=$error"));
                }
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
                
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = $blockInfo['message'];
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode($blockInfo['message']);
                    header('Location: ' . url("/auth/login?error=$error"));
                }
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
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = implode(', ', $errores);
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode(implode(', ', $errores));
                    header('Location: ' . url("/auth/login?error=$error"));
                }
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
                
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = 'Credenciales incorrectas';
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode('Credenciales incorrectas');
                    header('Location: ' . url("/auth/login?error=$error"));
                }
                exit;
            }

            // Verificar si el usuario está activo
            if (!$usuario['activo']) {
                SecurityLogger::log(SecurityLogger::LOGIN_FAIL, 'Intento de login con cuenta desactivada', [
                    'email' => $email,
                    'user_id' => $usuario['id']
                ]);
                
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = 'Tu cuenta está desactivada. Contacta al administrador.';
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode('Tu cuenta está desactivada. Contacta al administrador.');
                    header('Location: ' . url("/auth/login?error=$error"));
                }
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
                
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = 'Credenciales incorrectas';
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode('Credenciales incorrectas');
                    header('Location: ' . url("/auth/login?error=$error"));
                }
                exit;
            }
            
            // Éxito: resetear intentos fallidos
            LoginRateHelper::resetAttempts($email);

            // Obtener información del rol
            $rol = $this->rolModel->obtenerPorId($usuario['rol_id']);
            if (!$rol || !$rol['activo']) {
                // Si viene del carrito, redirigir con error en sesión
                if (!empty($redirect) && $redirect === 'carrito/ver') {
                    $_SESSION['auth_error'] = 'Tu rol está desactivado. Contacta al administrador.';
                    header('Location: ' . url('carrito/ver'));
                } else {
                    $error = urlencode('Tu rol está desactivado. Contacta al administrador.');
                    header('Location: ' . url("/auth/login?error=$error"));
                }
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

            // Redirigir según el parámetro redirect o al perfil por defecto
            if (!empty($redirect)) {
                header('Location: ' . url($redirect));
            } else {
                header('Location: ' . url('/auth/profile'));
            }
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

    /**
     * Mostrar formulario de registro
     */
    public function registro()
    {
        // Si ya está autenticado, redirigir al perfil o a la URL de redirección
        if (SessionHelper::isAuthenticated()) {
            $redirect = $_GET['redirect'] ?? '';
            if (!empty($redirect)) {
                header('Location: ' . url($redirect));
            } else {
                header('Location: ' . url('/auth/profile'));
            }
            exit;
        }

        $error = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';
        $redirect = $_GET['redirect'] ?? '';
        
        require_once __DIR__ . '/../views/auth/registro.php';
    }

    /**
     * Procesar registro de nuevo usuario
     */
    public function procesarRegistro()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/auth/registro'));
            exit;
        }

        try {
            // Verificar token CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (empty($csrfToken) || !CsrfHelper::validateToken($csrfToken, 'registro_form')) {
                SecurityLogger::log(SecurityLogger::CSRF_ERROR, 'Token CSRF inválido en registro', [
                    'email' => $_POST['email'] ?? 'no proporcionado'
                ]);
                $error = urlencode('Error de seguridad: Token inválido o expirado.');
                header('Location: ' . url("/auth/registro?error=$error"));
                exit;
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $redirect = $_POST['redirect'] ?? '';

            // Validaciones
            $errores = [];
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            } elseif (strlen($nombre) < 2) {
                $errores[] = 'El nombre debe tener al menos 2 caracteres';
            }

            if (empty($email)) {
                $errores[] = 'El email es requerido';
            } elseif (!Validator::email($email)) {
                $errores[] = 'El email no es válido';
            }

            if (empty($password)) {
                $errores[] = 'La contraseña es requerida';
            } elseif (strlen($password) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            if ($password !== $confirmPassword) {
                $errores[] = 'Las contraseñas no coinciden';
            }

            // Verificar si el email ya existe
            if (empty($errores)) {
                $usuarioExistente = $this->usuarioModel->obtenerPorEmail($email);
                if ($usuarioExistente) {
                    $errores[] = 'Ya existe un usuario con este email';
                }
            }

            if (!empty($errores)) {
                $error = urlencode(implode(', ', $errores));
                $redirectParam = !empty($redirect) ? '&redirect=' . urlencode($redirect) : '';
                header('Location: ' . url("/auth/registro?error=$error$redirectParam"));
                exit;
            }

            // Crear usuario
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $usuarioData = [
                'nombre' => $nombre,
                'email' => $email,
                'password' => $hashedPassword,
                'rol_id' => 2, // Cliente por defecto
                'activo' => 1
            ];

            $usuarioId = $this->usuarioModel->crear($usuarioData);
            
            if (!$usuarioId) {
                $error = urlencode('Error al crear el usuario');
                $redirectParam = !empty($redirect) ? '&redirect=' . urlencode($redirect) : '';
                header('Location: ' . url("/auth/registro?error=$error$redirectParam"));
                exit;
            }

            // Crear registro en usuario_detalles si las tablas están migradas
            try {
                $detallesSql = "INSERT INTO usuario_detalles (usuario_id) VALUES (?)";
                $stmt = \Core\Database::getConexion()->prepare($detallesSql);
                $stmt->execute([$usuarioId]);
            } catch (\Exception $e) {
                // Si falla, es porque las tablas no están migradas aún, continuamos
            }

            // Iniciar sesión automáticamente
            $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
            $rol = $this->rolModel->obtenerPorId($usuario['rol_id']);
            
            SessionHelper::login($usuario, $rol);
            $_SESSION['mostrar_popup'] = true;

            // Registrar evento
            SecurityLogger::log(SecurityLogger::LOGIN_SUCCESS, 'Usuario registrado e iniciado sesión exitosamente', [
                'user_id' => $usuarioId,
                'email' => $email,
                'auto_login' => true
            ]);

            // Redirigir según el parámetro redirect
            if (!empty($redirect)) {
                header('Location: ' . url($redirect));
            } else {
                header('Location: ' . url('/auth/profile'));
            }
            exit;

        } catch (\Exception $e) {
            error_log("Error en AuthController::procesarRegistro: " . $e->getMessage());
            $error = urlencode('Error interno del servidor');
            $redirectParam = !empty($redirect) ? '&redirect=' . urlencode($redirect) : '';
            header('Location: ' . url("/auth/registro?error=$error$redirectParam"));
            exit;
        }
    }

   /* ================================
     * LOGIN GOOGLE
     * ================================ */
    public function loginGoogle()
    {
        $google = new \Controllers\GoogleAuthController();
        $google->login();
    }

    /**
     * Callback de Google
     */
    public function googleCallback()
    {
        $google = new \Controllers\GoogleAuthController();
        $google->callback();
    }

    /* ================================
     * CAMBIO DE CONTRASEÑA
     * ================================ */

    /**
     * Mostrar formulario de cambio de contraseña
     */
    public function changePassword()
{
    if (!SessionHelper::isAuthenticated()) {
        header('Location: ' . url('/auth/login'));
        exit;
    }

    $usuario = SessionHelper::getUser();
    $usuarioDb = $this->usuarioModel->obtenerPorId($usuario['id']);

    // Detección de cuenta social o password hash largo
    $isSocial = false;
    $checks = ['google_id','facebook_id','auth_provider','provider','oauth_provider','provider_name','social_provider'];
    foreach ($checks as $k) {
        if (isset($usuarioDb[$k]) && !empty($usuarioDb[$k]) && $usuarioDb[$k] !== 'local') {
            $isSocial = true;
            break;
        }
    }

    // password vacío → social
    if (!isset($usuarioDb['password']) || empty($usuarioDb['password'])) {
        $isSocial = true;
    }

    // password muy largo (hash típico bcrypt/argon2 ≥ 50)
    if (isset($usuarioDb['password']) && strlen($usuarioDb['password']) >= 50) {
        $isSocial = true;
    }

    if ($isSocial) {
        $error = urlencode('No puedes cambiar la contraseña en cuentas vinculadas con Google o Facebook.');
        header('Location: ' . url('/auth/profile?error=' . $error));
        exit;
    }

    require_once __DIR__ . '/../views/auth/changePassword.php';
}
public function updatePassword()
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
        $usuarioDb = $this->usuarioModel->obtenerPorId($usuario['id']);

        // Bloquear si es social o si password es hash largo
        $isSocial = false;
        $checks = ['google_id','facebook_id','auth_provider','provider','oauth_provider','provider_name','social_provider'];
        foreach ($checks as $k) {
            if (isset($usuarioDb[$k]) && !empty($usuarioDb[$k]) && $usuarioDb[$k] !== 'local') {
                $isSocial = true;
                break;
            }
        }
        if (!isset($usuarioDb['password']) || empty($usuarioDb['password'])) {
            $isSocial = true;
        }
        if (isset($usuarioDb['password']) && strlen($usuarioDb['password']) >= 60) {
            $isSocial = true;
        }

        if ($isSocial) {
            $error = urlencode('No puedes cambiar la contraseña en cuentas vinculadas con Google o Facebook.');
            header('Location: ' . url('/auth/profile?error=' . $error));
            exit;
        }

        // aceptar distintos nombres de input en caso tu vista varíe
        $passwordActual = $_POST['password_actual'] ?? $_POST['actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? $_POST['nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? $_POST['confirmar'] ?? '';

        $errores = [];

        // Validar contraseña actual
        if (empty($passwordActual) || !password_verify($passwordActual, $usuarioDb['password'])) {
            $errores[] = 'La contraseña actual es incorrecta';
        }

        // Validar nueva contraseña
        if (empty($passwordNueva) || strlen($passwordNueva) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }

        if ($passwordNueva !== $passwordConfirm) {
            $errores[] = 'Las contraseñas nuevas no coinciden';
        }

        if (!empty($errores)) {
            $error = urlencode(implode(', ', $errores));
            header('Location: ' . url("/auth/changePassword?error=$error"));
            exit;
        }

        // Actualizar contraseña
        $hashedPassword = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $this->usuarioModel->actualizar($usuario['id'], [
            'password' => $hashedPassword
        ]);

        SecurityLogger::log(SecurityLogger::PASSWORD_CHANGE, 'Cambio de contraseña exitoso', [
            'user_id' => $usuario['id'],
            'email' => $usuario['email']
        ]);

        $success = urlencode('Contraseña cambiada correctamente');
        header('Location: ' . url("/auth/profile?success=$success"));
        exit;

    } catch (\Exception $e) {
        error_log("Error en AuthController::updatePassword: " . $e->getMessage());
        $error = urlencode('Error interno al cambiar contraseña');
        header('Location: ' . url("/auth/changePassword?error=$error"));
        exit;
    }
}

}
