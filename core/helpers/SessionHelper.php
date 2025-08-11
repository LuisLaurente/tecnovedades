<?php

namespace Core\Helpers;

use Core\Helpers\SecurityLogger;

class SessionHelper
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Iniciar sesión de usuario
     */
    public static function login($usuario, $rol)
    {
        self::start();

        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        // Guardar datos del usuario en la sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_role_id'] = $usuario['rol_id'];
        $_SESSION['user_role_name'] = $rol['nombre'];
        $_SESSION['user_role_description'] = $rol['descripcion'];
        
        // Decodificar permisos desde JSON
        $permisos = json_decode($rol['permisos'], true);
        $_SESSION['user_permissions'] = is_array($permisos) ? $permisos : [];
        
        $_SESSION['user_active'] = $usuario['activo'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['authenticated'] = true;
        
        // Almacenar la IP del usuario para detectar secuestros de sesión
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        
        // Almacenar el user-agent para detectar sesiones robadas
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        error_log("✅ Usuario {$usuario['email']} logueado con rol {$rol['nombre']} y permisos: " . implode(', ', $_SESSION['user_permissions']));
    }

    /**
     * Cerrar sesión
     */
    public static function logout()
    {
        self::start();

        // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated()
    {
        self::start();

        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            return false;
        }
        
        // Verificar si la IP ha cambiado (posible secuestro de sesión)
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            SecurityLogger::log(SecurityLogger::AUTH_ERROR, 'Posible secuestro de sesión - IP cambiada', [
                'user_id' => $_SESSION['user_id'] ?? 'desconocido',
                'user_email' => $_SESSION['user_email'] ?? 'desconocido',
                'original_ip' => $_SESSION['user_ip'],
                'current_ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            self::logout();
            return false;
        }
        
        // Verificar si el User-Agent ha cambiado (posible robo de sesión)
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            SecurityLogger::log(SecurityLogger::AUTH_ERROR, 'Posible secuestro de sesión - User-Agent cambiado', [
                'user_id' => $_SESSION['user_id'] ?? 'desconocido',
                'user_email' => $_SESSION['user_email'] ?? 'desconocido',
                'original_agent' => $_SESSION['user_agent'],
                'current_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            self::logout();
            return false;
        }

        // Verificar timeout de sesión (opcional - 2 horas)
        $timeout = 2 * 60 * 60; // 2 horas en segundos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            SecurityLogger::log(SecurityLogger::SESSION_EXPIRED, 'Sesión expirada por inactividad', [
                'user_id' => $_SESSION['user_id'] ?? 'desconocido',
                'user_email' => $_SESSION['user_email'] ?? 'desconocido',
                'elapsed_time' => time() - $_SESSION['last_activity']
            ]);
            
            self::logout();
            return false;
        }

        // Actualizar último tiempo de actividad
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Obtener datos del usuario actual
     */
    public static function getUser()
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nombre' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'rol_id' => $_SESSION['user_role_id'] ?? null,
            'activo' => $_SESSION['user_active'] ?? null,
        ];
    }

    /**
     * Obtener datos del rol actual
     */
    public static function getRole()
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_role_id'] ?? null,
            'nombre' => $_SESSION['user_role_name'] ?? null,
            'descripcion' => $_SESSION['user_role_description'] ?? null,
            'permisos' => $_SESSION['user_permissions'] ?? [],
        ];
    }

    /**
     * Obtener permisos del usuario actual
     */
    public static function getPermissions()
    {
        if (!self::isAuthenticated()) {
            return [];
        }

        return $_SESSION['user_permissions'] ?? [];
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public static function hasPermission($permiso)
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        $permisos = self::getPermissions();
        $hasPermission = in_array($permiso, $permisos);
        
        error_log("🔍 Verificando permiso '$permiso' para usuario: " . ($hasPermission ? 'SÍ' : 'NO'));
        
        return $hasPermission;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin()
    {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        // Verificar si es admin por nombre de rol o tiene permiso de admin
        return $_SESSION['user_role_name'] === 'admin' || 
               self::hasPermission('admin') || 
               self::hasPermission('usuarios');  // Los usuarios con permiso de gestión de usuarios se consideran admin
    }

    /**
     * Obtener ID del usuario actual
     */
    public static function getUserId()
    {
        $user = self::getUser();
        return $user['id'] ?? null;
    }

    /**
     * Obtener nombre del usuario actual
     */
    public static function getUserName()
    {
        $user = self::getUser();
        return $user['nombre'] ?? null;
    }

    /**
     * Obtener email del usuario actual
     */
    public static function getUserEmail()
    {
        $user = self::getUser();
        return $user['email'] ?? null;
    }

    /**
     * Obtener ID del rol actual
     */
    public static function getRoleId()
    {
        return $_SESSION['user_role_id'] ?? null;
    }

    /**
     * Obtener nombre del rol actual
     */
    public static function getRoleName()
    {
        return $_SESSION['user_role_name'] ?? null;
    }

    /**
     * Actualizar datos del usuario en la sesión
     */
    public static function updateUser($usuario)
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_active'] = $usuario['activo'];

        return true;
    }

    /**
     * Verificar si el usuario puede acceder a una sección
     */
    public static function canAccess($seccion)
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        // Los administradores pueden acceder a todo
        if (self::isAdmin()) {
            return true;
        }

        // Verificar permisos específicos
        return self::hasPermission($seccion);
    }

    /**
     * Obtener información completa de la sesión
     */
    public static function getSessionInfo()
    {
        return [
            'user_id' => self::getUserId(),
            'user_name' => self::getUserName(),
            'user_email' => self::getUserEmail(),
            'role_id' => self::getRoleId(),
            'role_name' => self::getRoleName(),
            'permissions' => self::getPermissions(),
            'is_authenticated' => self::isAuthenticated(),
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null
        ];
    }
}
