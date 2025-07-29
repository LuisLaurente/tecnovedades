<h2>🛒 Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= url('css/carrito.css') ?>">
<a href="<?= url('producto/index') ?>" class="boton-volver">🛒 Volverrrr</a>
<a href="<?= url('pedido/checkout') ?>" class="boton-checkout">Finalizar compra</a>


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
                    <td class="producto-talla"><?= htmlspecialchars($item['talla']) ?></td>
                    <td class="producto-color"><?= htmlspecialchars($item['color']) ?></td>
                    <td class="producto-precio">S/ <?= number_format($item['precio'], 2) ?></td>
                    <td>
                        <div class="cantidad-container">
                            <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-disminuir" title="Disminuir cantidad">➖</a>
                            <span class="cantidad-numero"><?= $item['cantidad'] ?></span>
                            <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-aumentar" title="Aumentar cantidad">➕</a>
                        </div>
                    </td>
                    <td class="producto-subtotal">S/ <?= number_format($item['subtotal'], 2) ?></td>
                    <td>
                        <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>"
                            class="btn-eliminar"
                            title="Eliminar producto"
                            onclick="return confirm('¿Eliminar este producto del carrito?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" class="total-label">💰 Total:</td>
                <td colspan="2" class="total-amount">S/ <?= number_format($totales['total'] ?? 0, 2) ?></td>
            </tr>

        </table>
        <div class="resumen-totales">
            <p>🧾 Subtotal: S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></p>
            <p>🎁 Descuento: S/ <?= number_format($totales['descuento'] ?? 0, 2) ?></p>
            <p><strong>💰 Total: S/ <?= number_format($totales['total'] ?? 0, 2) ?></strong></p>
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
        <div class="carrito-vacio-icon">🛒</div>
        <p>Tu carrito está vacío</p>
        <p style="font-size: 0.9rem; margin-top: 10px;">¡Agrega algunos productos para comenzar!</p>
    </div>
<?php endif; ?>