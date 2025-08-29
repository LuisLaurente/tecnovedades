<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cantidadEnCarrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += $item['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Bytebox</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?= url('css/carrito-sin-sesion.css') ?>">
</head>

<body class="carrito-sin-sesion">
    <div class="container-principal">
        <!-- Header -->
        <a href="<?= url('/') ?>" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver a la tienda
        </a>

        <div class="carrito-header">
            <h1 class="carrito-title">Tu Carrito</h1>
            <p class="carrito-subtitle">Inicia sesión para continuar con tu compra</p>
        </div>

        <div class="main-grid">
            <!-- Columna izquierda: Productos del carrito y autenticación -->
            <div class="left-column">
                <!-- Productos del carrito -->
                <div class="carrito-section">
                    <div class="productos-header">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h2 class="productos-title">Productos (<?= $cantidadEnCarrito ?>)</h2>
                    </div>

                    <div class="productos-list">
                        <?php if (!empty($productosDetallados)): ?>
                            <?php foreach ($productosDetallados as $item): ?>
                                <div class="producto-item">
                                    <div class="producto-info">
                                        <div class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                                        <div class="producto-detalles">
                                            <?php if (!empty($item['talla'])): ?>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Talla:</span>
                                                    <span><?= htmlspecialchars($item['talla']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['color'])): ?>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Color:</span>
                                                    <span><?= htmlspecialchars($item['color']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cantidad-precio">
                                            <div class="cantidad-container">
                                                <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-disminuir" title="Disminuir cantidad">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                                    </svg>
                                                </a>
                                                <span class="cantidad-numero"><?= $item['cantidad'] ?></span>
                                                <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-aumentar" title="Aumentar cantidad">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="precio-subtotal">
                                                <div class="precio-unitario">S/ <?= number_format($item['precio'], 2) ?> c/u</div>
                                                <div class="precio-total">S/ <?= number_format($item['subtotal'], 2) ?></div>
                                            </div>
                                            <div class="acciones-producto">
                                                <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>"
                                                    class="btn-eliminar"
                                                    title="Eliminar producto"
                                                    onclick="return confirm('¿Eliminar este producto del carrito?')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carrito-vacio">
                                <div class="carrito-vacio-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="60" height="60">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3>Tu carrito está vacío</h3>
                                <p>¡Agrega algunos productos para comenzar!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sección de autenticación -->
                <div class="auth-section">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="auth-title">Iniciar Sesión</h2>
                            <p class="auth-subtitle">¿Ya tienes una cuenta? Ingresa aquí</p>
                        </div>

                        <!-- Mensajes de error -->
                        <?php if (!empty($error)): ?>
                            <div class="alert error">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form class="login-form" method="POST" action="<?= url('/auth/authenticate') ?>">
                            <?= \Core\Helpers\CsrfHelper::tokenField('login_form') ?>
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars('carrito/ver') ?>">
                            
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input id="email" name="email" type="email" required placeholder="tu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input id="password" name="password" type="password" required placeholder="••••••••">
                            </div>

                            <div class="form-options">
                                <div class="remember-me">
                                    <input id="remember" name="remember" type="checkbox">
                                    <label for="remember">Recordarme</label>
                                </div>
                                <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                            </div>

                            <button type="submit" class="btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Iniciar Sesión
                            </button>
                        </form>

                        <a href="<?= url('auth/registro?redirect=' . urlencode('carrito/ver')) ?>" class="btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Crear Cuenta Nueva
                        </a>

                        <div class="social-divider">
                            <span>o continúa con</span>
                        </div>

                        <div class="social-buttons">
                            <a href="<?= url('auth/google') ?>" class="btn-social btn-google">
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Continuar con Google
                            </a>
                            
                            <a href="<?= url('auth/facebook') ?>" class="btn-social btn-facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877f2">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Continuar con Facebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Resumen de compra -->
            <?php if (!empty($productosDetallados)): ?>
                <div class="right-column">
                    <div class="resumen-compra">
                        <div class="resumen-header">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h3 class="resumen-title">Resumen de Compra</h3>
                        </div>

                        <div class="resumen-detalles">
                            <div class="resumen-item">
                                <span class="resumen-label">Subtotal (<?= $cantidadEnCarrito ?> <?= $cantidadEnCarrito == 1 ? 'item' : 'items' ?>):</span>
                                <span class="resumen-valor">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                            </div>

                            <?php if (($totales['descuento'] ?? 0) > 0): ?>
                                <div class="resumen-item descuento">
                                    <span class="resumen-label">Descuento aplicado:</span>
                                    <span class="resumen-valor">-S/ <?= number_format($totales['descuento'], 2) ?></span>
                                </div>
                            <?php endif; ?>

                            <hr class="resumen-divider">

                            <div class="resumen-item total-final">
                                <span class="resumen-label">Total:</span>
                                <span class="resumen-valor">S/ <?= number_format($totales['total'] ?? 0, 2) ?></span>
                            </div>
                        </div>

                        <div class="resumen-footer">
                            <div class="security-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Compra 100% segura</span>
                            </div>
                            <p class="resumen-info">
                                💡 <strong>Tip:</strong> Inicia sesión para guardar tus direcciones y hacer compras más rápidas en el futuro
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Enfocar el primer campo al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.focus();
            }
        });

        // Validación del lado del cliente
        const loginForm = document.querySelector('.login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                if (!email || !password) {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos');
                    return;
                }

                if (!email.includes('@') || !email.includes('.')) {
                    e.preventDefault();
                    alert('Por favor, ingresa un email válido');
                    return;
                }

                // Mostrar indicador de carga
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" opacity="0.75"/></svg> Iniciando sesión...';
                submitBtn.disabled = true;

                // Restaurar el botón después de 10 segundos como medida de seguridad
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        }

        // Animación suave para los elementos al hacer scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observar elementos para animación
        document.querySelectorAll('.producto-item, .auth-card, .resumen-compra').forEach(el => {
            observer.observe(el);
        });

        // Mejorar la experiencia de los botones de cantidad
        document.querySelectorAll('.btn-cantidad').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Añadir efecto visual
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Confirmación para eliminar productos
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productoNombre = this.closest('.producto-item').querySelector('.producto-nombre').textContent;
                
                if (confirm(`¿Eliminar "${productoNombre}" del carrito?`)) {
                    window.location.href = this.href;
                }
            });
        });
    </script>
</body>
</html>
