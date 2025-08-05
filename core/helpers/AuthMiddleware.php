<?php
namespace Core\Helpers;

class AuthMiddleware
{
    /**
     * Rutas públicas que no requieren autenticación
     */
    private static $publicRoutes = [
        'auth/login',
        'auth/authenticate',
        'error/notFound',
        'error/forbidden'
    ];

    /**
     * Verificar si la ruta actual requiere autenticación
     */
    public static function requiresAuth($url)
    {
        // Si la URL está vacía, requiere autenticación para redirigir al perfil
        if (empty($url)) {
            return true;
        }

        // Normalizar la URL
        $url = trim($url, '/');
        
        // Verificar si es una ruta pública
        foreach (self::$publicRoutes as $publicRoute) {
            if ($url === $publicRoute || strpos($url, $publicRoute) === 0) {
                return false;
            }
        }

        // Por defecto, todas las demás rutas requieren autenticación
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
            // Redirigir al login
            header('Location: ' . url('/auth/login'));
            exit;
        }

        // Verificar permisos específicos si es necesario
        return self::checkPermissions($url);
    }

    /**
     * Verificar permisos específicos para rutas administrativas
     */
    private static function checkPermissions($url)
    {
        $segments = explode('/', trim($url, '/'));
        $controller = $segments[0] ?? '';
        $action = $segments[1] ?? 'index';

        // Definir permisos requeridos por controlador
        $requiredPermissions = [
            'usuario' => 'gestionar_usuarios',
            'rol' => 'gestionar_roles',
            'categoria' => 'gestionar_categorias',
            'producto' => 'gestionar_productos',
            'pedido' => 'gestionar_pedidos',
            'cupon' => 'gestionar_cupones',
            'promocion' => 'gestionar_promociones',
            'etiqueta' => 'gestionar_etiquetas',
            'carga' => 'carga_masiva',
            'carrito' => 'gestionar_carrito'
        ];

        // Verificar si el controlador requiere permisos específicos
        if (isset($requiredPermissions[$controller])) {
            $permission = $requiredPermissions[$controller];
            
            if (!SessionHelper::hasPermission($permission)) {
                // Redirigir a página de error de permisos
                header('Location: ' . url('/error/forbidden'));
                exit;
            }
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
