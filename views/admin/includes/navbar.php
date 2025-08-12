<!-- Sidebar moderno con diseño claro y pastel -->
<?php
// Función helper para verificar permisos
function hasPermission($permission) {
    return \Core\Helpers\SessionHelper::hasPermission($permission);
}

// Función para verificar si el usuario es un cliente (rol usuario)
function isCliente() {
    $userRole = \Core\Helpers\SessionHelper::getRole();
    
    // Si el rol es un array, obtener el nombre
    if (is_array($userRole) && isset($userRole['nombre'])) {
        return $userRole['nombre'] === 'usuario';
    }
    
    // Si es una cadena, verificar directamente
    if (is_string($userRole)) {
        return $userRole === 'usuario';
    }
    
    // Verificar por permisos - los clientes solo tienen 'perfil'
    $userPermissions = \Core\Helpers\SessionHelper::getPermissions();
    if (is_array($userPermissions)) {
        // Cliente típico: solo tiene permiso de 'perfil' y no tiene permisos administrativos
        return in_array('perfil', $userPermissions) && 
               !in_array('usuarios', $userPermissions) && 
               !in_array('productos', $userPermissions);
    }
    
    return false;
}

// Obtener información del usuario
$userName = \Core\Helpers\SessionHelper::getUserName();
$userEmail = \Core\Helpers\SessionHelper::getUserEmail();
$userRole = \Core\Helpers\SessionHelper::getRole();
?>
<aside class="w-64 h-screen bg-gradient-to-b from-slate-50 to-blue-50 border-r border-blue-100 shadow-lg hidden lg:flex lg:flex-col">
    <!-- Header del sidebar con logo y nombre -->
    <div class="p-6 border-b border-blue-100 bg-white/50 backdrop-blur-sm flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    TecnoVedades
                </h1>
                <p class="text-xs text-gray-500">Panel de Control</p>
            </div>
        </div>
    </div>

    <!-- Navegación principal - scrolleable -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        
        <!-- Mi Perfil (siempre visible para usuarios autenticados) -->
        <a href="<?= url('/auth/profile') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="font-medium">Mi Perfil</span>
        </a>

        <!-- Divisor -->
        <div class="my-4 border-t border-blue-200/50"></div>
        
        <!-- Sección Vista Cliente (solo para usuarios con rol 'usuario') -->
        <?php if (isCliente()): ?>
        <div class="mb-3">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Vista Cliente</h3>
        </div>

        <!-- Mis Pedidos -->
        <a href="<?= url('/usuario/pedidos') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-violet-400 to-violet-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <span class="font-medium">📦 Mis Pedidos</span>
        </a>

        <!-- Divisor solo si es cliente -->
        <div class="my-4 border-t border-blue-200/50"></div>
        <?php endif; ?>
        
        <!-- Sección Panel Administrativo (solo para usuarios con permisos administrativos) -->
        <?php 
        $tienePermisosAdmin = hasPermission('usuarios') || hasPermission('productos') || hasPermission('categorias') || hasPermission('pedidos');
        if ($tienePermisosAdmin): 
        ?>
        <div class="mb-3">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Panel Administrativo</h3>
        </div>
        <?php endif; ?>

        <!-- Gestión de Usuarios (solo admin) -->
        <?php if (hasPermission('usuarios')): ?>
        <a href="<?= url('/usuario') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"></path>
                </svg>
            </div>
            <span class="font-medium">Usuarios</span>
        </a>
        <?php endif; ?>

        <!-- Roles -->
        <?php if (hasPermission('usuarios')): ?>
        <a href="<?= url('/rol') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <span class="font-medium">Roles</span>
        </a>
        <?php endif; ?>

        <!-- Productos -->
        <?php if (hasPermission('productos')): ?>
        <a href="<?= url('/producto') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <span class="font-medium">Productos</span>
        </a>
        <?php endif; ?>

        <!-- Categorías -->
        <?php if (hasPermission('categorias')): ?>
        <a href="<?= url('/categoria') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-pink-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <span class="font-medium">Categorías</span>
        </a>
        <?php endif; ?>

        <!-- Etiquetas -->
        <?php if (hasPermission('productos')): ?>
        <a href="<?= url('/etiqueta') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <span class="font-medium">Etiquetas</span>
        </a>
        <?php endif; ?>

        <!-- Pedidos -->
        <?php if (hasPermission('pedidos')): ?>
        <a href="<?= url('/pedido/listar') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <span class="font-medium">Pedidos</span>
        </a>
        <?php endif; ?>

        <!-- Cupones -->
        <?php if (hasPermission('cupones')): ?>
        <a href="<?= url('/cupon') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <span class="font-medium">Cupones</span>
        </a>
        <?php endif; ?>

        <!-- Promociones -->
        <?php if (hasPermission('promociones')): ?>
        <a href="<?= url('/promocion') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <span class="font-medium">Promociones</span>
        </a>
        <?php endif; ?>

        <!-- Popup Promocional -->
        <?php if (hasPermission('promociones')): ?>
        <a href="<?= url('/adminpopup') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a3 3 0 01-3 3H6a3 3 0 01-3-3V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                </svg>
            </div>
            <span class="font-medium">Popup</span>
        </a>
        <?php endif; ?>

        <!-- Carga Masiva -->
        <?php if (hasPermission('productos')): ?>
        <a href="<?= url('/cargamasiva') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-teal-400 to-teal-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <span class="font-medium">Carga Masiva</span>
        </a>
        <?php endif; ?>

        <!-- Reportes/Estadísticas -->
        <?php if (hasPermission('reportes')): ?>
        <a href="<?= url('/adminreclamacion') ?>" class="nav-link group flex items-center p-3 text-gray-700 hover:bg-white/60 rounded-xl transition-all duration-200 backdrop-blur-sm border border-transparent hover:border-blue-200">
            <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <span class="font-medium">Reportes</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Sección de información del usuario -->
    <div class="p-4 border-t border-blue-100 bg-white/30 backdrop-blur-sm flex-shrink-0">
        <div class="flex items-center p-3 bg-white/60 rounded-xl shadow-sm mb-3 backdrop-blur-sm">
            <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center mr-3">
                <span class="text-sm font-bold text-white">
                    <?= strtoupper(substr($userName ?? 'U', 0, 1)) ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                    <?= htmlspecialchars($userName ?? 'Usuario') ?>
                </p>
                <p class="text-xs text-gray-500 truncate">
                    <?= htmlspecialchars($userRole['nombre'] ?? 'Sin rol') ?>
                </p>
            </div>
        </div>

        <!-- Debug de permisos (solo en desarrollo) -->
        <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs">
            <p class="font-semibold text-yellow-800 mb-1">🔍 Debug Permisos</p>
            <p><strong>Permisos:</strong></p>
            <ul class="list-disc list-inside ml-2">
                <?php foreach (\Core\Helpers\SessionHelper::getPermissions() as $permission): ?>
                    <li><?= htmlspecialchars($permission) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</aside>

<!-- Overlay para móviles (cuando el sidebar esté abierto) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden"></div>

<!-- Botón hamburguesa para móviles -->
<button id="mobile-menu-button" class="lg:hidden fixed top-4 left-4 z-30 p-2 bg-white rounded-lg shadow-lg">
    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- Script para funcionalidad móvil -->
<script>
    // Funcionalidad del menú móvil
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('sidebar-overlay');

    mobileMenuButton?.addEventListener('click', function() {
        sidebar?.classList.toggle('hidden');
        overlay?.classList.toggle('hidden');
    });

    overlay?.addEventListener('click', function() {
        sidebar?.classList.add('hidden');
        overlay?.classList.add('hidden');
    });
</script>