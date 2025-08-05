<?php

namespace Middleware;

use Core\Helpers\SessionHelper;

class AuthMiddleware
{
    /**
     * Verificar que el usuario esté autenticado
     */
    public static function requireAuth()
    {
        if (!SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }
    }

    /**
     * Verificar que el usuario tenga un permiso específico
     */
    public static function requirePermission($permiso)
    {
        self::requireAuth();
        
        if (!SessionHelper::hasPermission($permiso)) {
            header('Location: ' . url('/auth/dashboard?error=' . urlencode('No tienes permisos para acceder a esta sección')));
            exit;
        }
    }

    /**
     * Verificar que el usuario sea administrador
     */
    public static function requireAdmin()
    {
        self::requireAuth();
        
        if (!SessionHelper::isAdmin()) {
            header('Location: ' . url('/auth/dashboard?error=' . urlencode('Se requieren permisos de administrador')));
            exit;
        }
    }

    /**
     * Verificar que el usuario NO esté autenticado (para páginas como login)
     */
    public static function requireGuest()
    {
        if (SessionHelper::isAuthenticated()) {
            header('Location: ' . url('/auth/dashboard'));
            exit;
        }
    }

    /**
     * Aplicar middleware según el tipo
     */
    public static function apply($type, $permission = null)
    {
        switch ($type) {
            case 'auth':
                self::requireAuth();
                break;
            case 'admin':
                self::requireAdmin();
                break;
            case 'permission':
                if ($permission) {
                    self::requirePermission($permission);
                }
                break;
            case 'guest':
                self::requireGuest();
                break;
        }
    }
}
