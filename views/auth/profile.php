<!-- Sidebar moderno con diseño claro y pastel -->
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Bytebox</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS de la página de perfil -->
    <link rel="stylesheet" href="<?= url('css/profile.css' ) ?>">
</head>
<body>

    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <span><?= strtoupper(substr($userName ?? 'U', 0, 1)) ?></span>
            </div>
            <h1 class="profile-name"><?= htmlspecialchars($userName ?? 'Usuario') ?></h1>
            <p class="profile-email"><?= htmlspecialchars($userEmail ?? 'email@dominio.com') ?></p>
            <a href="<?= url('/') ?>" class="back-link">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Volver a la tienda
            </a>
        </div>
<!-- Sección Vista Cliente (solo para usuarios con rol 'usuario') -->
        <?php if (isCliente()): ?>
            <div class="mb-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Vista Cliente</h3>
            </div>

            <!-- Divisor solo si es cliente -->
            <div class="my-4 border-t border-blue-200/50"></div>
        <?php endif; ?>
        <!-- Mensajes de éxito o error -->
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Columna Izquierda: Formulario de Información Personal -->
            <div class="profile-card">
                <h3 class="card-title">📝 Información Personal</h3>
                <form method="POST" action="<?= url('/auth/updateProfile') ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>

            <!-- Columna Derecha: Acciones y Seguridad -->
            <div class="profile-card">
                <h3 class="card-title">🚀 Acciones Rápidas</h3>
                
                <!-- Botón Mis Pedidos (solo para clientes) -->
                <?php if (isCliente()): ?>
                <div class="action-group">
                    <a href="<?= url('/usuario/pedidos') ?>" class="btn btn-full btn-secondary">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Mis Pedidos
                    </a>
                </div>
                <?php endif; ?>

                <!-- Botón Cerrar Sesión -->
                <div class="action-group">
                     <a href="<?= url('/auth/logout') ?>" class="btn btn-full btn-danger">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Cerrar Sesión
                    </a>
                </div>

                <h3 class="card-title" style="margin-top: 2rem;">🔒 Seguridad</h3>
                <div class="info-group">
                    <label>Contraseña</label>
                    <div class="info-value">Protegida con encriptación</div>
                </div>
                <div class="form-actions">
                    <a href="#" class="btn btn-secondary">Cambiar Contraseña</a>
                </div>
            </div>
        </div>

        <!-- Sección de Permisos (si no es cliente) -->
        <?php if (!isCliente() && !empty($userPermissions)): ?>
            <div class="profile-card permissions-card">
                <h3 class="card-title">🔑 Permisos Asignados</h3>
                <div class="permissions-list">
                    <?php foreach ($userPermissions as $permiso): ?>
                        <span class="permission-tag"><?= htmlspecialchars($permiso) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

</body>
</html>
