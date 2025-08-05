<!-- Sidebar moderno con diseño claro y pastel -->
<?php


// Incluir el helper de autenticación
//require_once __DIR__ . '/auth_helper.php';

//$nombre = $_SESSION['usuario_nombre'] ?? 'Invitado';
//$email = $_SESSION['usuario_email'] ?? 'correo@ejemplo.com';
//$roleId = $_SESSION['usuario_role_id'] ?? null;
//$roleName = $_SESSION['usuario_rol'] ?? 'Sin rol';

// Instanciar el helper de autenticación
//$auth = new AuthHelper($pdo);
?>
<aside class="w-64 h-screen bg-gradient-to-b from-slate-50 to-blue-50 border-r border-blue-100 shadow-lg hidden lg:flex lg:flex-col">
    <!-- Header del sidebar con logo y nombre -->
    <div class="p-6 border-b border-blue-100 bg-white/50 backdrop-blur-sm flex-shrink-0">
        <div class="flex items-center space-x-3">
            <!-- Imagen de logo personalizada -->
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg overflow-hidden bg-gradient-to-br from-blue-400 to-purple-500">
                <!-- <img src="../../img/tienda.png" alt="Logo Moda Salud" class="w-full h-full object-cover" /> -->
                <!-- Si no hay imagen, se muestra el SVG por defecto -->
                <!--
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                -->
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-800">Tienda Tecnovedades</h1>
                <p class="text-xs text-gray-500">Equipo de Desarrollo</p>
            </div>
        </div>
    </div>

    <!-- Navegación principal con scroll -->
    <nav class="mt-6 px-4 space-y-2 flex-1 overflow-y-auto pb-4">
        <!-- Productos -->
        <a href="<?= url('producto/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-green-100 hover:to-emerald-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <span class="font-medium">Productos</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Categorías -->
        <a href="<?= url('categoria/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-blue-100 hover:to-indigo-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <span class="font-medium">Categorías</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Usuarios -->
        <a href="<?= url('usuario/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-orange-100 hover:to-yellow-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-yellow-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.121 2.121 0 11-3 3m3-3a2.121 2.121 0 01-3 3m3-3v6"></path>
                </svg>
            </div>
            <span class="font-medium">Usuarios</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Roles -->
        <a href="<?= url('rol/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-indigo-100 hover:to-purple-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <span class="font-medium">Roles</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Pedidos -->
        <a href="<?= url('pedido/listar'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-purple-100 hover:to-pink-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <span class="font-medium">Pedidos</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Promociones -->
        <a href="<?= url('promocion/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-red-100 hover:to-rose-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-red-400 to-rose-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="font-medium">Promociones</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <!-- Cupones -->
        <a href="<?= url('cupon/index'); ?>" class="group flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-teal-100 hover:to-cyan-100 hover:shadow-md transition-all duration-300 ease-in-out transform hover:scale-105">
            <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <span class="font-medium">Cupones</span>
            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
    </nav>

    <!-- Sección de configuración (parte inferior) - fija -->
    <div class="p-4 border-t border-blue-100 bg-white/30 backdrop-blur-sm flex-shrink-0">
        <!-- Perfil del usuario -->
        <div class="flex items-center p-3 bg-white/60 rounded-xl shadow-sm mb-3 backdrop-blur-sm">
            <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="flex-1">
              <!--  <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($nombre) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($email) ?></p> -->
            </div>
        </div>

        <!-- Configuración -->
        <a href="../config/config.php" class="group flex items-center px-4 py-3 text-gray-600 rounded-xl hover:bg-gradient-to-r hover:from-gray-100 hover:to-slate-100 hover:shadow-md transition-all duration-300">
            <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-slate-500 rounded-lg flex items-center justify-center mr-3 shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <span class="font-medium text-sm">Configuración</span>
        </a>
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