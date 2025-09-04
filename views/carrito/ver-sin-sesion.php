<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Este cálculo inicial es para la carga de la página,
// pero será actualizado dinámicamente por AJAX.
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
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    <div class="container-principal">
        <!-- Header con botón de volver -->
        <a href="<?= url('/') ?>" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver a la tienda
        </a>

        <!-- Layout principal de dos columnas -->
        <div class="main-grid">
            <!-- Columna izquierda: Formulario de login (sin cambios ) -->
            <div class="left-column">
                <div class="auth-section">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="auth-title">Iniciar Sesión</h2>
                        </div>

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
                        <div class="social-divider"><span>o continúa con</span></div>
                        <div class="social-buttons">
                            <a href="<?= url('auth/google') ?>" class="btn-social btn-google">
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                Continuar con Google
                            </a>
                            <a href="<?= url('auth/facebook') ?>" class="btn-social btn-facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877f2">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                                Continuar con Facebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Header del carrito y resumen -->
            <div class="right-column">
                <div class="carrito-header">
                    <h1 class="carrito-title">Resumen de la compra</h1>
                </div>

                <!-- Contenedor principal del carrito que se actualizará -->
                <div id="carrito-container">
                    <?php if (!empty($productosDetallados)): ?>
                        <div class="carrito-section">
                            <!-- ID para el contenedor de la lista de productos -->
                            <div class="productos-list" id="productos-list-container">
                                <?php foreach ($productosDetallados as $item): ?>
                                    <!-- ID único para cada fila de producto -->
                                    <div class="producto-item" id="producto-<?= htmlspecialchars($item['clave']) ?>">
                                        <div class="producto-imagen">
                                            <?php if (!empty($item['imagen'])): ?>
                                                <img src="<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" class="producto-img"> <?php else: ?>
                                                <div class="producto-img-placeholder">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="producto-info">
                                            <div class="producto-cantidad">
                                                <div class="cantidad-container">
                                                    <!-- Atributo data-clave para identificar el producto en JS -->
                                                    <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-disminuir" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Disminuir cantidad">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                                        </svg>
                                                    </a>
                                                    <!-- ID único para el número de cantidad -->
                                                    <span class="cantidad-numero" id="cantidad-<?= htmlspecialchars($item['clave']) ?>"><?= $item['cantidad'] ?></span>
                                                    <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-aumentar" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Aumentar cantidad">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="producto-detalles">
                                                <div class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                                                <div class="producto-sku">SKU: <?= htmlspecialchars($item['sku'] ?? 'N/A') ?></div>
                                                <div class="producto-variantes">
                                                    <?php if (!empty($item['color'])): ?><span class="variante">COLOR <?= strtoupper(htmlspecialchars($item['color'])) ?></span><?php endif; ?>
                                                    <?php if (!empty($item['talla'])): ?><span class="variante">TALLA CALZADO <?= htmlspecialchars($item['talla']) ?></span><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="producto-precio">
                                                <div class="precio-info">
                                                    <span class="precio-label">Internet</span>
                                                    <span class="precio-valor">S/ <?= number_format($item['precio'], 2) ?></span>
                                                </div>
                                            </div>
                                            <div class="producto-acciones">
                                                <!-- Se quita el onclick, ahora se maneja en JS -->
                                                <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>" class="btn-eliminar" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Eliminar producto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Resumen de compra -->
                        <div class="resumen-compra">
                            <div class="resumen-header">
                                <h3 class="resumen-title">Resumen</h3>
                            </div>
                            <div class="resumen-detalles">
                                <div class="resumen-item">
                                    <span class="resumen-label">Subtotal</span>
                                    <!-- ID único para el subtotal -->
                                    <span class="resumen-valor" id="resumen-subtotal">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                                </div>
                                <div class="resumen-item total-final">
                                    <span class="resumen-label">Total</span>
                                    <!-- ID único para el total -->
                                    <span class="resumen-valor" id="resumen-total">S/ <?= number_format($totales['total'] ?? 0, 2) ?></span>
                                </div>
                            </div>
                            <div class="resumen-footer">
                                <button class="btn-continuar" disabled>Continuar</button>
                                <div class="seguir-comprando"><a href="<?= url('/') ?>" class="link-seguir">Seguir comprando</a></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Carrito vacío -->
                        <div class="carrito-vacio">
                            <div class="carrito-vacio-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="60" height="60">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3>Tu carrito está vacío</h3>
                            <p>¡Agrega algunos productos para comenzar!</p>
                            <a href="<?= url('/') ?>" class="btn-volver-tienda">Volver a la tienda</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- CÓDIGO JS ORIGINAL (LOGIN, ANIMACIONES, ETC.) ---
            // (Este código no se modifica)
            const emailInput = document.getElementById('email');
            if (emailInput) emailInput.focus();
            // ... resto de tu código original para el formulario de login, etc.

            // --- NUEVA IMPLEMENTACIÓN AJAX PARA EL CARRITO ---

            const carritoContainer = document.getElementById('carrito-container');
            // Formateador para mostrar los precios en formato de moneda local (Soles)
            const formatter = new Intl.NumberFormat('es-PE', {
                style: 'currency',
                currency: 'PEN'
            });

            // Función genérica para realizar peticiones fetch
            async function realizarPeticionAjax(url, clave, action) {
                const productoItem = document.getElementById(`producto-${clave}`);
                if (productoItem) productoItem.style.opacity = '0.5'; // Indicador visual de carga

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) throw new Error(`Error del servidor: ${response.statusText}`);

                    const result = await response.json();

                    if (result.success) {
                        // La acción fue exitosa, actualizamos la vista
                        actualizarVistaCarrito(result.data);

                        // Comprobamos si un producto fue eliminado (cantidad <= 0)
                        const itemExists = result.data.itemDetails && result.data.itemDetails[clave];
                        if ((action === 'disminuir' || action === 'eliminar') && !itemExists) {
                            eliminarProductoDeVista(clave);
                        }
                    } else {
                        // El servidor respondió con un error de lógica (ej. no hay stock)
                        alert('Aviso: ' + (result.message || 'No se pudo actualizar el carrito.'));
                        actualizarVistaCarrito(result.data); // Aun así actualizamos por si hubo otros cambios
                    }

                } catch (error) {
                    console.error('Error en la petición AJAX:', error);
                    alert('Hubo un problema de conexión. Por favor, recarga la página.');
                    if (productoItem) productoItem.style.opacity = '1'; // Restaurar en caso de error de red
                }
            }

            // Función para actualizar el DOM con los nuevos datos del carrito
            function actualizarVistaCarrito(data) {
                const subtotalEl = document.getElementById('resumen-subtotal');
                const totalEl = document.getElementById('resumen-total');

                if (subtotalEl) subtotalEl.textContent = formatter.format(data.totals.subtotal || 0);
                if (totalEl) totalEl.textContent = formatter.format(data.totals.total || 0);

                // Actualizar cantidades de cada item visible
                if (data.itemDetails) {
                    for (const clave in data.itemDetails) {
                        const item = data.itemDetails[clave];
                        const cantidadEl = document.getElementById(`cantidad-${clave}`);
                        if (cantidadEl) {
                            cantidadEl.textContent = item.cantidad;
                            const productoItem = document.getElementById(`producto-${clave}`);
                            if (productoItem) productoItem.style.opacity = '1'; // Restaurar opacidad
                        }
                    }
                }
            }

            // Función para eliminar visualmente un producto del DOM
            function eliminarProductoDeVista(clave) {
                const productoItem = document.getElementById(`producto-${clave}`);
                if (productoItem) {
                    productoItem.style.transition = 'all 0.4s ease-out';
                    productoItem.style.opacity = '0';
                    productoItem.style.transform = 'translateX(-50px)';

                    setTimeout(() => {
                        productoItem.remove();
                        // Si el contenedor de productos queda vacío, recargamos la página
                        // para que el backend muestre el mensaje de "carrito vacío".
                        const productosContainer = document.getElementById('productos-list-container');
                        if (productosContainer && productosContainer.children.length === 0) {
                            window.location.reload();
                        }
                    }, 400);
                }
            }

            // Delegación de eventos: un solo listener para todo el carrito
            if (carritoContainer) {
                carritoContainer.addEventListener('click', function(e) {
                    const target = e.target.closest('a[data-clave]');
                    if (!target) return;

                    e.preventDefault(); // ¡Importante! Previene la recarga de la página

                    const clave = target.dataset.clave;
                    let url, action;

                    if (target.classList.contains('btn-aumentar')) {
                        action = 'aumentar';
                        url = `<?= url('carrito/aumentar/') ?>${encodeURIComponent(clave)}`;
                        realizarPeticionAjax(url, clave, action);
                    }

                    if (target.classList.contains('btn-disminuir')) {
                        action = 'disminuir';
                        url = `<?= url('carrito/disminuir/') ?>${encodeURIComponent(clave)}`;
                        realizarPeticionAjax(url, clave, action);
                    }

                    if (target.classList.contains('btn-eliminar')) {
                        if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                            action = 'eliminar';
                            url = `<?= url('carrito/eliminar/') ?>${encodeURIComponent(clave)}`;
                            realizarPeticionAjax(url, clave, action);
                        }
                    }
                });
            }
        });
    </script>

    <!-- Estilos adicionales para animaciones (sin cambios) -->
    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        .btn-cantidad {
            transition: all 0.2s ease;
        }

        .btn-cantidad:active {
            transform: scale(0.95);
        }

        .producto-item {
            transition: all 0.3s ease;
        }

        .producto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
</body>

</html>