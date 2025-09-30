<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/profile.css') ?>">

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="main-wrapper">
        <!-- Barra lateral -->
        <div class="sidebar-fixed">
            <?php include __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
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

                <!-- Información Personal -->
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

                <!-- Seguridad -->
                <?php
                $isSocial = false;
                if (isset($usuario['password']) && strlen($usuario['password']) >= 50) {
                    $isSocial = true;
                }
                ?>

                <div class="profile-card">
                    <div class="password-card">
                        <h3>Seguridad</h3>

                        <?php if (!$isSocial): ?>
                            <a href="<?= url('/auth/changePassword') ?>" class="button orders-button">
                                Cambiar contraseña
                            </a>
                        <?php else: ?>
                            <div class="button-disabled">
                                Cambiar contraseña (No disponible)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mis Pedidos -->
                <div class="orders-card">
                    <h3>Mis Pedidos</h3>
                    <a href="<?= url('/usuario/pedidos') ?>" class="button orders-button">
                        Ver mis pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
</body>
</html>