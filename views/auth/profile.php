<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/profile.css') ?>">

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>

        <div class="flex-1 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto main-content">
                <!-- El sticky header superior se manejaba en la plantilla -->
                <div class="sticky top-0 z-40"></div>

                <div class="profile-container">
                    <h1>Mi Cuenta</h1>

                    <!-- Mensajes -->
                    <?php if (!empty($_GET['success'])): ?>
                        <div class="message success-message">
                            <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_GET['error'])): ?>
                        <div class="message error-message">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-card">
                        <h3>Información Personal</h3>
                        <form method="POST" action="<?= url('/auth/updateProfile') ?>">
                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input type="text"
                                    name="nombre"
                                    id="nombre"
                                    value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="email">Correo electrónico</label>
                                <input type="email"
                                    name="email"
                                    id="email"
                                    value="<?= htmlspecialchars($usuario['email']) ?>"
                                    required readonly>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="button primary-button">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- Botón Cambiar Contraseña (seguridad + protección front) -->
                    <?php
                    // Detectar si es cuenta social por longitud del password
                    $isSocial = false;
                    if (isset($usuario['password']) && strlen($usuario['password']) >= 50) {
                        $isSocial = true;
                    }
                    ?>

                    <!-- Botón Cambiar Contraseña -->
                    <div class="profile-card">
                        <div class="password-card">
                            <h3>Seguridad</h3>

                            <?php if (!$isSocial): ?>
                                <!-- Enlace activo -->
                                <a href="<?= url('/auth/changePassword') ?>"
                                class="button orders-button inline-block text-center">
                                    Cambiar contraseña
                                </a>
                            <?php else: ?>
                                <!-- Botón gris deshabilitado -->
                                <div
                                id="change-pass-disabled"
                                role="button"
                                aria-disabled="true"
                                tabindex="-1"
                                class="block w-full text-center rounded-md px-4 py-2 text-sm font-semibold bg-gray-300 text-gray-700 select-none mx-auto"
                                style="pointer-events: none;">
                                Cambiar contraseña (No disponible)
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>




                    <!-- Botón Mis Pedidos para clientes -->
                    <div class="orders-card">
                        <h3>Mis Pedidos</h3>
                        <a href="<?= url('/usuario/pedidos') ?>" class="button orders-button">
                            Ver mis pedidos
                        </a>
                    </div>
                </div>

                
            </main>
        </div>
    </div>
    <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
</body>

</html>
