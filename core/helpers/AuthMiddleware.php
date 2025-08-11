<?php

namespace Core\Helpers;

use Core\Helpers\SecurityLogger;

class AuthMiddleware
{
    /**
     * Rutas públicas que no requieren autenticación
     */
    private static $publicRoutes = [
        'home/index',
        'home/buscar',
        'home/detalleproducto',
        'auth/login',
        'auth/authenticate',
        'error/notFound',
        'error/forbidden',
        'carrito/agregar',
        'carrito/ver',
        'carrito/actualizar',
        'carrito/eliminar',
        // Rutas públicas para OAuth
        'googleauth/login',
        'auth/google-callback',
        // Buscador de productos público
        'producto/autocomplete',
        'producto/busqueda',
    ];

    /**
     * Mapeo específico de controladores/acciones a permisos requeridos
     */
    private static $permissionMap = [
        'usuario' => 'usuarios',
        'rol' => 'usuarios', // Los roles están dentro de gestión de usuarios
        'categoria' => 'categorias',
        'producto' => 'productos',
        'etiqueta' => 'productos', // Las etiquetas están relacionadas con productos
        'pedido' => 'pedidos',
        'cupon' => 'cupones',
        'promocion' => 'promociones',
        'cargamasiva' => 'productos', // Carga masiva es para productos
        'adminreclamacion' => 'reportes',
        'estadisticas' => 'reportes',
        'adminpopup' => 'promociones' // Popup promocional
    ];

    /**
     * Verificar si la ruta actual requiere autenticación
     */
    public static function requiresAuth($url)
    {
        if (empty($url)) {
            return true;
        }

        $url = trim($url, '/');

        // Verificar si es una ruta pública
        foreach (self::$publicRoutes as $publicRoute) {
            if ($url === $publicRoute || strpos($url, $publicRoute) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar autenticación y permisos
     */
    public static function checkAuth($url)
    {
        // Si la ruta no requiere autenticación, permitir acceso
        if (!self::requiresAuth($url)) {
            return true;
        }

        // Verificar si el usuario está autenticado
        if (!SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }

        // Verificar permisos específicos
        return self::checkPermissions($url);
    }

    /**
     * Verificar permisos específicos para rutas administrativas
     */
    private static function checkPermissions($url)
    {
        $segments = explode('/', trim($url, '/'));
        $controller = strtolower($segments[0] ?? '');

        // Rutas que solo requieren autenticación (sin permisos específicos)
        $authOnlyRoutes = ['auth', 'home'];
        if (in_array($controller, $authOnlyRoutes)) {
            return true;
        }

        // Verificar permisos específicos según el mapeo
        if (isset(self::$permissionMap[$controller])) {
            $requiredPermission = self::$permissionMap[$controller];

            // Verificar si el usuario tiene el permiso requerido
            if (!SessionHelper::hasPermission($requiredPermission)) {
                $usuario = SessionHelper::getUser();
                $rol = SessionHelper::getRole();

                // Registrar intento de acceso denegado
                SecurityLogger::log(SecurityLogger::ACCESS_DENIED, "Acceso denegado a '{$controller}'", [
                    'user_id' => $usuario['id'] ?? 'desconocido',
                    'email' => $usuario['email'] ?? 'desconocido',
                    'rol' => $rol['nombre'] ?? 'desconocido',
                    'permission_required' => $requiredPermission,
                    'url' => $url
                ]);

                error_log("❌ Usuario sin permiso '$requiredPermission' para acceder a '$controller'");
                header('Location: ' . url('/error/forbidden'));
                exit;
            }
        } else {
            // Para controladores no mapeados explícitamente, registrar una advertencia
            error_log("⚠️ Controlador no mapeado en permisos: '$controller'");
        }

        return true;
    }

    /**
     * Verificar si el usuario puede acceder a un recurso específico
     */
    public static function canAccess($resource, $action = 'read')
    {
        if (!SessionHelper::isAuthenticated()) {
            return false;
        }

        // Mapear recursos a permisos
        $resourcePermissions = [
            'usuarios' => 'gestionar_usuarios',
            'roles' => 'gestionar_roles',
            'categorias' => 'gestionar_categorias',
            'productos' => 'gestionar_productos',
            'pedidos' => 'gestionar_pedidos',
            'cupones' => 'gestionar_cupones',
            'promociones' => 'gestionar_promociones',
            'etiquetas' => 'gestionar_etiquetas',
            'carga_masiva' => 'carga_masiva',
            'carrito' => 'gestionar_carrito'
        ];

        $permission = $resourcePermissions[$resource] ?? null;

        if ($permission) {
            return SessionHelper::hasPermission($permission);
        }

        // Si no se especifica permiso, permitir acceso si está autenticado
        return true;
    }

    /**
     * Verificar si el usuario tiene rol de administrador
     */
    public static function isAdmin()
    {
        if (!SessionHelper::isAuthenticated()) {
            return false;
        }

        $user = SessionHelper::getUser();
        $rol = SessionHelper::getRole();

        return $rol && (
            $rol['nombre'] === 'admin' ||
            $rol['nombre'] === 'administrador' ||
            SessionHelper::hasPermission('administrar_sistema')
        );
    }

    /**
     * Middleware para proteger rutas de administrador
     */
    public static function requireAdmin()
    {
        if (!self::isAdmin()) {
            $usuario = SessionHelper::getUser();

            SecurityLogger::log(SecurityLogger::ACCESS_DENIED, "Intento de acceso a área de administrador", [
                'user_id' => $usuario['id'] ?? 'desconocido',
                'email' => $usuario['email'] ?? 'desconocido',
                'url' => $_SERVER['REQUEST_URI'] ?? 'desconocida'
            ]);

            header('Location: ' . url('/error/forbidden'));
            exit;
        }
    }

    /**
     * Verificar si el usuario puede modificar el recurso
     */
    public static function canModify($resource, $resourceId = null)
    {
        if (!SessionHelper::isAuthenticated()) {
            return false;
        }

        // Los administradores pueden modificar todo
        if (self::isAdmin()) {
            return true;
        }

        // Para usuarios normales, verificar permisos específicos
        return self::canAccess($resource, 'write');
    }
}
