<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$cantidadEnCarrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += $item['cantidad'];
    }
} ?> 
<!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">
    
    <div class="header-container"> <?php include_once __DIR__ . '/../admin/includes/header.php'; ?> </div>
<h2>Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= url('css/carrito.css') ?>">

<!-- Contenido del carrito -->
<div class="clearfix"></div>
<?php if (!empty($productosDetallados)): ?>
    <div class="carrito-container">
        <div class="tabla-container">
            <table class="carrito-tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Detalles</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productosDetallados as $item): ?>
                        <tr class="producto-fila">
                            <td class="producto-info">
                                <div class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                            </td>
                            <td class="producto-detalles">
                                <div class="detalle-item">
                                    <span class="detalle-label">Tamaño:</span>
                                    <span class="detalle-valor"><?= htmlspecialchars($item['talla'] ?? 'N/A') ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label">Color:</span>
                                    <span class="detalle-valor"><?= htmlspecialchars($item['color'] ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td class="producto-precio">
                                <span class="precio-valor">S/ <?= number_format($item['precio'], 2) ?></span>
                            </td>
                            <td class="producto-cantidad">
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
                            </td>
                            <td class="producto-subtotal">
                                <span class="subtotal-valor">S/ <?= number_format($item['subtotal'], 2) ?></span>
                            </td>
                            <td class="producto-acciones">
                                <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>"
                                    class="btn-eliminar"
                                    title="Eliminar producto"
                                    onclick="return confirm('¿Eliminar este producto del carrito?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="resumen-container">
            <div class="resumen-totales">
                <div class="resumen-item">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Subtotal:
                    </div>
                    <div class="resumen-valor">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Descuento:
                    </div>
                    <div class="resumen-valor">S/ <?= number_format($totales['descuento'] ?? 0, 2) ?></div>
                </div>

                <?php if (isset($totales['cupon_aplicado'])): ?>
                    <div class="resumen-item cupon-aplicado">
                        <div class="resumen-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M9 9h1.023M9 13h1.023M9 17h1.023M7.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196zM16.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196z" />
                            </svg>
                            Cupón aplicado:
                        </div>
                        <div class="resumen-valor">
                            <strong><?= htmlspecialchars($totales['cupon_aplicado']['codigo']) ?></strong>
                            <?php if (!empty($totales['cupon_aplicado']['usuarios_autorizados'])): ?>
                                <span class="cupon-restringido">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Restringido
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="resumen-item total-final">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Total:
                    </div>
                    <div class="resumen-valor">S/ <?= number_format($totales['total'] ?? 0, 2) ?></div>
                </div>
            </div>

            <?php if (!empty($promocionesAplicadas)): ?>
                <div class="promociones-aplicadas">
                    <h4>Promociones aplicadas:</h4>
                    <ul>
                        <?php foreach ($promocionesAplicadas as $promo): ?>
                            <li><?= htmlspecialchars($promo['promocion']['nombre']) ?> (<?= $promo['accion']['tipo'] ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="carrito-vacio">
        <div class="carrito-vacio-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="60" height="60">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="carrito-vacio-content">
            <h3>Tu carrito está vacío</h3>
            <p>¡Agrega algunos productos para comenzar!</p>
        </div>
    </div>
<?php endif; ?>

<div class="acciones-carrito">
    <a href="<?= url('/') ?>" class="boton-volver">🛒 Volver</a>
    <?php if (!empty($cantidadEnCarrito) && $cantidadEnCarrito > 0): ?>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="<?= url('pedido/checkout') ?>" class="boton-checkout">Finalizar compra</a>
        <?php else: ?>
            <a href="<?= url('pedido/precheckout') ?>" class="boton-checkout">Continuar compra</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
