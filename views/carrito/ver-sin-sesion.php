<?php
if (session_status() === PHP_SESSION_NONE) {
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
    <?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
    <title>Carrito de Compras - Bytebox</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Enlace al archivo CSS externo -->
    <link rel="stylesheet" href="<?= url('css/carrito-sin-sesion.css') ?>">
    
    
</head>

<body class="carrito-sin-sesion">

    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="container-principal">
        <h2 class="page-title">Carrito de Compras</h2>

        <?php if (!empty($productosDetallados)): ?>
            <div class="main-grid">
                <!-- Columna Izquierda: Lista de Productos -->
                <div class="productos-container">
                    <div class="productos-list-header">
                        Tus Productos
                    </div>
                    <div id="productos-list">
                        <?php foreach ($productosDetallados as $item): ?>
                            <div class="producto-item" id="producto-<?= htmlspecialchars($item['clave']) ?>">
                                <div class="producto-info-wrapper">
                                    <div class="producto-imagen">
                                        <img src="<?= htmlspecialchars($item['imagen'] ?? 'ruta/a/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                                    </div>
                                    <div class="producto-info">
                                        <div class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                                        <div class="producto-precio">S/ <?= number_format($item['precio'], 2) ?></div>
                                    </div>
                                </div>
                                <div class="producto-actions-wrapper">
                                    <div class="cantidad-container">
                                        <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-disminuir" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Disminuir">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                            </svg>
                                        </a>
                                        <span class="cantidad-numero" id="cantidad-<?= htmlspecialchars($item['clave']) ?>"><?= $item['cantidad'] ?></span>
                                        <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-aumentar" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Aumentar">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="producto-subtotal" id="subtotal-<?= htmlspecialchars($item['clave']) ?>">
                                        S/ <?= number_format($item['subtotal'], 2) ?>
                                    </div>
                                    <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>" class="btn-eliminar" data-clave="<?= htmlspecialchars($item['clave']) ?>" title="Eliminar producto">
                                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Columna Derecha: Resumen de Compra -->
                <div class="resumen-container">
                    <div class="resumen-header">
                        <h3>Resumen de Compra</h3>
                    </div>
                    <div class="resumen-body">
                        <div class="resumen-item">
                            <span class="resumen-label">Subtotal:</span>
                            <span class="resumen-valor" id="resumen-subtotal">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                        </div>
                        <?php if (!empty($promocionesAplicadas) && $totales['descuento'] > 0): ?>
                            <div class="resumen-item descuentos-detalle">
                                <span class="resumen-label">Descuentos:</span>
                                <div class="descuentos-lista">
                                    <?php foreach ($promocionesAplicadas as $promocion): ?>
                                        <?php if (is_numeric($promocion['monto']) && $promocion['monto'] > 0): ?>
                                            <div class="descuento-item">
                                                <div class="descuento-nombre">
                                                    <?= htmlspecialchars($promocion['nombre']) ?>
                                                </div>
                                                <div class="descuento-monto">
                                                    - S/ <?= number_format($promocion['monto'], 2) ?>
                                                </div>
                                            </div>
                                        <?php elseif ($promocion['monto'] === 'Gratis'): ?>
                                            <div class="descuento-item">
                                                <div class="descuento-nombre">
                                                    <?= htmlspecialchars($promocion['nombre']) ?>
                                                </div>
                                                <div class="descuento-monto envio-gratis">
                                                    Envío Gratis
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Si no hay promociones aplicadas, mostrar la línea simple de descuento -->
                            <div class="resumen-item">
                                <span class="resumen-label">Descuento</span>
                                <span class="resumen-valor-descuento" id="resumen-descuento">
                                    S/ <?= number_format($totales['descuento'] ?? 0, 2) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        

                        <div class="resumen-item total-final">
                            <span class="resumen-label">Total</span>
                            <span class="resumen-valor" id="resumen-total">S/ <?= number_format($totales['total'] ?? 0, 2) ?></span>
                        </div>
                        <!-- Sección de Cupones -->
                        <div class="resumen-item cupon-seccion">
                            <div class="cupon-titulo">
                                ¿Tienes un cupón?
                            </div>
                            <form id="form-cupon" method="POST" action="<?= url('carrito/aplicarCupon') ?>" class="cupon-input-container">
                                <input type="text" name="codigo" id="codigo-cupon" placeholder="Ingresa tu código" value="<?= htmlspecialchars($_SESSION['cupon_aplicado']['codigo'] ?? '') ?>" <?= isset($_SESSION['cupon_aplicado']) ? 'readonly' : '' ?>>
                                <?php if (isset($_SESSION['cupon_aplicado'])): ?>
                                    <a href="<?= url('carrito/quitarCupon') ?>" class="btn-cupon btn-remover">Remover</a>
                                <?php else: ?>
                                    <button type="submit" class="btn-cupon btn-aplicar">Aplicar</button>
                                <?php endif; ?>
                            </form>
                            <div id="mensaje-cupon" class="cupon-mensaje">
                                <?php if (isset($_SESSION['mensaje_cupon_exito'])): ?>
                                    <span class="cupon-exito">✓ <?= htmlspecialchars($_SESSION['mensaje_cupon_exito']) ?></span>
                                    <?php unset($_SESSION['mensaje_cupon_exito']); ?>
                                <?php elseif (isset($_SESSION['mensaje_cupon_error'])): ?>
                                    <span class="cupon-error">✗ <?= htmlspecialchars($_SESSION['mensaje_cupon_error']) ?></span>
                                    <?php unset($_SESSION['mensaje_cupon_error']); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['cupon_aplicado']) && !empty($totales['descuento_cupon'])): ?>
                            <div class="resumen-item cupon-aplicado-detalle">
                                <span class="resumen-label">Descuento Cupón</span>
                                <span class="resumen-valor" id="resumen-descuento-cupon">
                                    - S/ <?= number_format($totales['descuento_cupon'], 2) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="acciones-carrito">
                <a href="<?= url('home/busqueda') ?>" class="boton-volver">Seguir Comprando</a>
                <?php if ($cantidadEnCarrito > 0): ?>
                    <button class="boton-checkout" id="btn-finalizar-compra">Finalizar Compra</button>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="carrito-vacio">
                <div class="carrito-vacio-icon">
                    <img src="<?= url('image/carrito.svg') ?>" alt="Carrito vacío" style="width: 60px; height: 60px; display: block; margin: 0 auto;">
                </div>

                <h3>Tu carrito está vacío</h3>
                <p>¡Agrega algunos productos para comenzar!</p>
                <a href="<?= url('/') ?>" class="boton-volver">Ir a la tienda</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Login -->
    <div id="modal-login" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Iniciar Sesión para Continuar</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <?php if (!empty($error)): ?>
                    <div class="alert error" style="margin-bottom: 20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" method="POST" action="<?= url('/auth/authenticate') ?>">
                    <?= \Core\Helpers\CsrfHelper::tokenField('login_form') ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars('pedido/checkout') ?>">

                    <div class="form-group">
                        <label for="modal-email">Correo Electrónico</label>
                        <input id="modal-email" name="email" type="email" required placeholder="tu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="modal-password">Contraseña</label>
                        <input id="modal-password" name="password" type="password" required placeholder="••••••••">
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input id="modal-remember" name="remember" type="checkbox">
                            <label for="modal-remember">Recordarme</label>
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

                <a href="<?= url('auth/registro?redirect=' . urlencode('pedido/checkout')) ?>" class="btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Crear Cuenta Nueva
                </a>

                <div class="social-divider"><span>o continúa con</span></div>

                <div class="social-buttons">
                    <!-- Los enlaces de login social ahora abren en la misma ventana -->
                    <a href="<?= url('auth/google?redirect=' . urlencode('pedido/checkout')) ?>" class="btn-social btn-google">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        Continuar con Google
                    </a>
                    <a href="<?= url('auth/facebook?redirect=' . urlencode('pedido/checkout')) ?>" class="btn-social btn-facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877f2">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Continuar con Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts para AJAX, Cupones y Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo del carrito (AJAX)
            const productosList = document.getElementById('productos-list');
            if (productosList) {
                productosList.addEventListener('click', function(e) {
                    const target = e.target.closest('a[data-clave]');
                    if (!target) return;

                    e.preventDefault();
                    const clave = target.dataset.clave;
                    let url;

                    if (target.classList.contains('btn-aumentar')) url = `<?= url('carrito/aumentar/') ?>${encodeURIComponent(clave)}`;
                    if (target.classList.contains('btn-disminuir')) url = `<?= url('carrito/disminuir/') ?>${encodeURIComponent(clave)}`;
                    if (target.classList.contains('btn-eliminar')) {
                        if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                            url = `<?= url('carrito/eliminar/') ?>${encodeURIComponent(clave)}`;
                        } else {
                            return;
                        }
                    }

                    if (url) realizarPeticionAjax(url, clave);
                });
            }

            async function realizarPeticionAjax(url, clave) {
                const productoItem = document.getElementById(`producto-${clave}`);
                if (productoItem) productoItem.style.opacity = '0.5';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();

                    if (result.success) {
                        actualizarVista(result.data);
                    } else {
                        alert(result.message || 'Ocurrió un error');
                        if (productoItem) productoItem.style.opacity = '1';
                    }
                } catch (error) {
                    console.error('Error AJAX:', error);
                    alert('Error de conexión. Por favor, recarga la página.');
                    if (productoItem) productoItem.style.opacity = '1';
                }
            }

            function actualizarVista(data) {
                const formatter = new Intl.NumberFormat('es-PE', {
                    style: 'currency',
                    currency: 'PEN'
                });

                // Actualizar totales del resumen
                document.getElementById('resumen-subtotal').textContent = formatter.format(data.totals.subtotal || 0);
                document.getElementById('resumen-descuento').textContent = formatter.format(data.totals.descuento || 0);
                document.getElementById('resumen-total').textContent = formatter.format(data.totals.total || 0);

                const descuentoCuponEl = document.getElementById('resumen-descuento-cupon');
                if (descuentoCuponEl) {
                    if (data.totals.descuento_cupon > 0) {
                        descuentoCuponEl.parentElement.style.display = 'flex';
                        descuentoCuponEl.textContent = `- ${formatter.format(data.totals.descuento_cupon)}`;
                    } else {
                        descuentoCuponEl.parentElement.style.display = 'none';
                    }
                }

                // Actualizar cada producto
                let itemsEnRespuesta = new Set();
                if (data.itemDetails) {
                    for (const clave in data.itemDetails) {
                        const item = data.itemDetails[clave];
                        itemsEnRespuesta.add(clave);

                        const cantidadEl = document.getElementById(`cantidad-${clave}`);
                        const subtotalEl = document.getElementById(`subtotal-${clave}`);
                        const productoItem = document.getElementById(`producto-${clave}`);

                        if (cantidadEl) cantidadEl.textContent = item.cantidad;
                        if (subtotalEl) subtotalEl.textContent = formatter.format(item.subtotal);
                        if (productoItem) productoItem.style.opacity = '1';
                    }
                }

                // Eliminar productos que ya no están en la respuesta
                const todosLosItemsEnDOM = document.querySelectorAll('.producto-item');
                todosLosItemsEnDOM.forEach(itemEl => {
                    const claveItem = itemEl.id.replace('producto-', '');
                    if (!itemsEnRespuesta.has(claveItem)) {
                        itemEl.style.transition = 'all 0.4s ease';
                        itemEl.style.opacity = '0';
                        itemEl.style.maxHeight = '0px';
                        itemEl.style.paddingTop = '0';
                        itemEl.style.paddingBottom = '0';
                        itemEl.style.marginTop = '0';
                        itemEl.style.marginBottom = '0';
                        itemEl.style.overflow = 'hidden';
                        setTimeout(() => itemEl.remove(), 400);
                    }
                });

                // Si no quedan productos, recargar para mostrar el mensaje de "carrito vacío"
                if (itemsEnRespuesta.size === 0) {
                    setTimeout(() => window.location.reload(), 500);
                }
            }

            // Manejo del Modal de Login
            const modal = document.getElementById('modal-login');
            const btnFinalizar = document.getElementById('btn-finalizar-compra');
            const closeModal = document.querySelector('.close-modal');

            function openModal() {
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            }

            function closeModalFunc() {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.body.style.overflow = 'auto';
            }

            if (btnFinalizar) {
                btnFinalizar.addEventListener('click', function() {
                    openModal();
                });
            }

            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    closeModalFunc();
                });
            }

            // Cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModalFunc();
                }
            });

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.style.display === 'block') {
                    closeModalFunc();
                }
            });

            // Prevenir que los enlaces del modal cierren el modal inmediatamente
            document.querySelectorAll('.modal-body a').forEach(link => {
                link.addEventListener('click', function(e) {
                    // No cerrar el modal para enlaces de login social
                    if (this.classList.contains('btn-social') || this.classList.contains('btn-secondary')) {
                        return; // Permitir que el enlace funcione normalmente
                    }
                });
            });
        });
    </script>

</body>

</html>