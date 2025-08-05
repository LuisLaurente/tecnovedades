<?php

namespace Core\Helpers;

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
        $_SESSION['user_permissions'] = json_decode($rol['permisos'], true) ?: [];
        $_SESSION['user_active'] = $usuario['activo'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['authenticated'] = true;
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

        // Verificar timeout de sesión (opcional - 2 horas)
        $timeout = 2 * 60 * 60; // 2 horas en segundos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
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
        $permisos = self::getPermissions();
        return in_array($permiso, $permisos);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin()
    {
        return self::hasPermission('admin') || $_SESSION['user_role_name'] === 'admin';
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
}
