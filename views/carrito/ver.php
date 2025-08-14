<h2>Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= url('css/carrito.css') ?>">
<a href="<?= url('/') ?>" class="boton-volver">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Volver
</a>
<a href="<?= url('pedido/checkout') ?>" class="boton-checkout">
    Finalizar compra
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
    </svg>
</a>


<!-- Contenido del carrito -->
<div class="clearfix"></div>
<?php if (!empty($productosDetallados)): ?>
    <div class="tabla-container">
        <table>
            <tr>
                <th>Producto</th>
                <th>Talla</th>
                <th>Color</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Eliminar</th>
            </tr>
            <?php foreach ($productosDetallados as $item): ?>
                <tr>
                    <td class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></td>
                    <td class="producto-talla"><?= htmlspecialchars($item['talla']?? '') ?></td>
                    <td class="producto-color"><?= htmlspecialchars($item['color']?? '') ?></td>
                    <td class="producto-precio">S/ <?= number_format($item['precio'], 2) ?></td>
                    <td>
                        <div class="cantidad-container">
                            <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-disminuir" title="Disminuir cantidad">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                </svg>
                            </a>
                            <span class="cantidad-numero"><?= $item['cantidad'] ?></span>
                            <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-aumentar" title="Aumentar cantidad">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </a>
                        </div>
                    </td>
                    <td class="producto-subtotal">S/ <?= number_format($item['subtotal'], 2) ?></td>
                    <td>
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
            <tr class="total-row">
                <td colspan="5" class="total-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Total:
                </td>
                <td colspan="2" class="total-amount">S/ <?= number_format($totales['total'] ?? 0, 2) ?></td>
            </tr>

        </table>
        <div class="resumen-totales">
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Subtotal: S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?>
            </p>
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Descuento: S/ <?= number_format($totales['descuento'] ?? 0, 2) ?>
            </p>
            <?php if (isset($totales['cupon_aplicado'])): ?>
                <p class="cupon-aplicado">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M9 9h1.023M9 13h1.023M9 17h1.023M7.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196zM16.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196z" />
                    </svg>
                    Cupón aplicado: <strong><?= htmlspecialchars($totales['cupon_aplicado']['codigo']) ?></strong>
                    <?php if (!empty($totales['cupon_aplicado']['usuarios_autorizados'])): ?>
                        <span class="cupon-restringido">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Restringido
                        </span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <p><strong>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Total: S/ <?= number_format($totales['total'] ?? 0, 2) ?>
            </strong></p>
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
<?php else: ?>
    <div class="carrito-vacio">
        <div class="carrito-vacio-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="60" height="60">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <p>Tu carrito está vacío</p>
        <p style="font-size: 0.9rem; margin-top: 10px;">¡Agrega algunos productos para comenzar!</p>
    </div>
<?php endif; ?>