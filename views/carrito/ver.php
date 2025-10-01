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
    <?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Bytebox</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Enlace al archivo CSS externo -->
    <link rel="stylesheet" href="<?= url('css/carrito.css') ?>">
</head>

<body>

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
                                        <!-- Asegúrate de tener una imagen placeholder o de que $item['imagen'] siempre exista -->
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
                            <span class="resumen-label">Subtotal</span>
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
                            <!-- Formulario para manejar el cupón -->
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
                <a href="<?= url('/') ?>" class="boton-volver">Seguir Comprando</a>
                <?php if ($cantidadEnCarrito > 0): ?>
                    <a href="<?= url(isset($_SESSION['usuario']) ? 'pedido/checkout' : 'pedido/precheckout') ?>" class="boton-checkout">Finalizar Compra</a>
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

    <!-- Scripts para AJAX y Cupones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>

</body>

</html>