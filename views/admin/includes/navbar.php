<?php
// Función helper para verificar permisos
function hasPermission($permission)
{
    return \Core\Helpers\SessionHelper::hasPermission($permission);
}

// Función para verificar si el usuario es un cliente (rol usuario)
function isCliente()
{
    $userRole = \Core\Helpers\SessionHelper::getRole();

    if (is_array($userRole) && isset($userRole['nombre'])) {
        return $userRole['nombre'] === 'usuario';
    }

    if (is_string($userRole)) {
        return $userRole === 'usuario';
    }

    $userPermissions = \Core\Helpers\SessionHelper::getPermissions();
    if (is_array($userPermissions)) {
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
<link rel="stylesheet" href="<?= url('/css/navbar.css') ?>">
<?php if (!isCliente()): ?>
    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="sidebar">
        <!-- Header del sidebar con logo y nombre -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1 class="logo-title">ByteBox</h1>
                    <p class="logo-subtitle">Panel de Control</p>
                </div>
            </div>
        </div>

        <!-- Navegación principal - scrolleable -->
        <nav class="sidebar-nav">

            <!-- Sección Panel Administrativo -->
            <?php
            $tienePermisosAdmin = hasPermission('usuarios') || hasPermission('productos') || hasPermission('categorias') || hasPermission('pedidos');
            if ($tienePermisosAdmin):
            ?>
                <div class="nav-section">
                    <h3 class="nav-section-title">Panel Administrativo</h3>
                </div>
            <?php endif; ?>

            <!-- Gestión de Usuarios -->
            <?php if (hasPermission('usuarios')): ?>
                <a href="<?= url('/usuario') ?>" class="nav-link">
                    <div class="nav-icon-wrapper user-iconAdmin">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Usuarios</span>
                </a>
            <?php endif; ?>

            <!-- Roles -->
            <?php if (hasPermission('usuarios')): ?>
                <a href="<?= url('/rol') ?>" class="nav-link">
                    <div class="nav-icon-wrapper role-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Roles</span>
                </a>
            <?php endif; ?>

            <!-- Productos -->
            <?php if (hasPermission('productos')): ?>
                <a href="<?= url('/producto') ?>" class="nav-link">
                    <div class="nav-icon-wrapper product-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Productos</span>
                </a>
            <?php endif; ?>

            <!-- Categorías -->
            <?php if (hasPermission('categorias')): ?>
                <a href="<?= url('/categoria') ?>" class="nav-link">
                    <div class="nav-icon-wrapper category-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Categorías</span>
                </a>
            <?php endif; ?>

            <!-- Etiquetas -->
            <?php if (hasPermission('productos')): ?>
                <a href="<?= url('/etiqueta') ?>" class="nav-link">
                    <div class="nav-icon-wrapper tag-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Etiquetas</span>
                </a>
            <?php endif; ?>

            <!-- Pedidos -->
            <?php if (hasPermission('pedidos')): ?>
                <a href="<?= url('/pedido/listar') ?>" class="nav-link">
                    <div class="nav-icon-wrapper order-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Pedidos</span>
                </a>
            <?php endif; ?>

            <!-- Cupones -->
            <?php if (hasPermission('cupones')): ?>
                <a href="<?= url('/cupon') ?>" class="nav-link">
                    <div class="nav-icon-wrapper coupon-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Cupones</span>
                </a>
            <?php endif; ?>

            <!-- Promociones -->
            <?php if (hasPermission('promociones')): ?>
                <a href="<?= url('/promocion') ?>" class="nav-link">
                    <div class="nav-icon-wrapper promotion-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Promociones</span>
                </a>
            <?php endif; ?>

            <!-- Popup Promocional -->
            <?php if (hasPermission('promociones')): ?>
                <a href="<?= url('/adminpopup') ?>" class="nav-link">
                    <div class="nav-icon-wrapper popup-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a3 3 0 01-3 3H6a3 3 0 01-3-3V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Popup</span>
                </a>
            <?php endif; ?>

            <!-- Banners -->
            <?php if (hasPermission('promociones') || hasPermission('banners')): ?>
                <a href="<?= url('/banner') ?>" class="nav-link">
                    <div class="nav-icon-wrapper banner-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l6 6 4-4 8 8"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Banners</span>
                </a>
            <?php endif; ?>

            <!-- Carga Masiva -->
            <?php if (hasPermission('productos')): ?>
                <a href="<?= url('/cargamasiva') ?>" class="nav-link">
                    <div class="nav-icon-wrapper bulk-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Carga Masiva</span>
                </a>
            <?php endif; ?>

            <!-- Reportes de Reclamaciones -->
            <?php if (hasPermission('reportes')): ?>
                <a href="<?= url('/adminreclamacion') ?>" class="nav-link">
                    <div class="nav-icon-wrapper claim-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Reportes de Reclamaciones</span>
                </a>

                <!-- Reseñas -->
                <a href="<?= url('/review') ?>" class="nav-link">
                    <div class="nav-icon-wrapper review-icon">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.382 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.382-2.46a1 1 0 00-1.176 0l-3.382 2.46c-.785.57-1.84-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.045 9.394c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.288-3.967z"/>
                        </svg>
                    </div>
                    <span class="nav-text">Reseñas</span>
                </a>

                <!-- Reportes de Ventas -->
                <a href="<?= url('/reporte/resumen') ?>" class="nav-link">
                    <div class="nav-icon-wrapper sales-icon">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V9a4 4 0 118 0v2m-4 4h.01M5 13h4m0 0V9m0 4v4m6-4h4"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Reportes de Ventas</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Sección de información del usuario -->
        <div class="sidebar-footer">

            <div class="user-info">
                <div class="user-avatar">
                    <span class="user-initial">
                        <?= strtoupper(substr($userName ?? 'U', 0, 1)) ?>
                    </span>
                </div>
                <div class="user-details">
                    <p class="user-name"><?= htmlspecialchars($userName ?? 'Usuario') ?></p>
                    <p class="user-role"><?= htmlspecialchars($userRole['nombre'] ?? 'Sin rol') ?></p>
                </div>
            </div>

            <!-- Debug de permisos (solo en desarrollo) -->
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                <div class="debug-info">
                    <p class="debug-title">🔍 Debug Permisos</p>
                    <p><strong>Permisos:</strong></p>
                    <ul class="debug-list">
                        <?php foreach (\Core\Helpers\SessionHelper::getPermissions() as $permission): ?>
                            <li><?= htmlspecialchars($permission) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Overlay para móviles -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- Botón hamburguesa para móviles -->
    <button id="mobile-menu-button" class="mobile-menu-button">
        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
<?php endif; ?>

<!-- Script para funcionalidad móvil -->
<script>
    // Funcionalidad del menú móvil
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    mobileMenuButton?.addEventListener('click', function() {
        sidebar?.classList.toggle('sidebar-open');
        overlay?.classList.toggle('overlay-open');
    });

    overlay?.addEventListener('click', function() {
        sidebar?.classList.remove('sidebar-open');
        overlay?.classList.remove('overlay-open');
    });
</script>