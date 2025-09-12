<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/ver.css') ?>">

<body>
    <?php
    $estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    ?>

    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">

            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <!-- Incluir header superior fijo -->
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="ver-pedido-page flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <a href="<?= url('pedido/listar') ?>" class="back-button">
                        Volver al listado
                    </a>

                    <div class="order-container">
                        <div class="order-header">
                            <h1 class="order-title">Detalle del Pedido #<?= $pedido['id'] ?></h1>
                            <p class="order-subtitle">Información completa y gestión del pedido</p>
                        </div>

                        <div class="order-content">
                            <div class="order-grid">
                                <!-- Panel de información -->
                                <div class="info-panel">
                                    <!-- Información del usuario -->
                                    <div class="section">
                                        <h3 class="section-title client-info">Información del Usuario</h3>
                                        <div class="client-card">
                                            <div class="client-row">
                                                <span class="client-label">Usuario ID:</span>
                                                <span class="client-value"><?= htmlspecialchars($pedido['cliente_id']) ?></span>
                                            </div>
                                            <div class="client-row">
                                                <span class="client-label">Fecha del pedido:</span>
                                                <span class="client-value"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></span>
                                            </div>
                                            <?php if (isset($direccion_pedido) && $direccion_pedido): ?>
                                                <div class="client-row">
                                                    <span class="client-label">Dirección de Envío:</span>
                                                    <span class="client-value"><?= htmlspecialchars($direccion_pedido) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Detalles del pedido -->
                                    <!-- Detalles del pedido -->
                                    <div class="section">
                                        <h3 class="section-title order-details">Detalles del Pedido</h3>
                                        <div class="order-details-grid">
                                            <div class="detail-item">
                                                <div class="detail-label">Estado</div>
                                                <div class="detail-value"><?= ucfirst($pedido['estado']) ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Subtotal</div>
                                                <div class="detail-value">S/ <?= number_format($pedido['subtotal'], 2) ?></div>
                                            </div>

                                            <!-- MOSTRAR DESCUENTOS POR PROMOCIONES -->
                                            <?php if (!empty($pedido['descuento_promocion']) && $pedido['descuento_promocion'] > 0): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label">Descuento por Promociones</div>
                                                    <div class="detail-value text-success">-S/ <?= number_format($pedido['descuento_promocion'], 2) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- MOSTRAR DESCUENTOS POR CUPÓN -->
                                            <?php if (!empty($pedido['descuento_cupon']) && $pedido['descuento_cupon'] > 0): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label">Descuento por Cupón</div>
                                                    <div class="detail-value text-success">-S/ <?= number_format($pedido['descuento_cupon'], 2) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- MOSTRAR COSTO DE ENVÍO -->
                                            <div class="detail-item">
                                                <div class="detail-label">Costo de Envío</div>
                                                <div class="detail-value <?= $pedido['costo_envio'] == 0 ? 'text-success' : '' ?>">
                                                    <?= $pedido['costo_envio'] == 0 ? 'Gratis' : 'S/ ' . number_format($pedido['costo_envio'], 2) ?>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Monto Total</div>
                                                <div class="detail-value total">S/ <?= number_format($pedido['monto_total'], 2) ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CUPÓN APLICADO -->
                                    <?php if (!empty($cupon_info)): ?>
                                        <div class="section">
                                            <h3 class="section-title products">Cupón Aplicado</h3>
                                            <div class="coupons-list">
                                                <div class="coupon-item">
                                                    <span class="coupon-code"><?= htmlspecialchars($cupon_info['codigo']) ?></span>
                                                    <span class="coupon-discount">
                                                        <?php if ($cupon_info['tipo'] == 'porcentaje'): ?>
                                                            -<?= $cupon_info['valor'] ?>%
                                                        <?php else: ?>
                                                            -S/ <?= number_format($cupon_info['valor'], 2) ?>
                                                        <?php endif; ?>
                                                        (Aplicado: S/ <?= number_format($cupon_info['descuento_aplicado'], 2) ?>)
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- PROMOCIONES APLICADAS -->
                                    <?php if (!empty($promociones_aplicadas)): ?>
    <div class="section">
        <h3 class="section-title products">Promociones Aplicadas</h3>
        <div class="promotions-list">
            <?php foreach ($promociones_aplicadas as $promocion): ?>
                <div class="promotion-item">
                    <span class="promotion-name"><?= htmlspecialchars($promocion['nombre']) ?></span>
                    <?php 
                    $monto_a_mostrar = null;

                    // Si el monto de la promoción individual está definido y es numérico o "Gratis"
                    if (isset($promocion['monto']) && ($promocion['monto'] === 'Gratis' || (is_numeric($promocion['monto']) && $promocion['monto'] > 0))) {
                        $monto_a_mostrar = $promocion['monto'];
                    } 
                    // Para pedidos antiguos donde el monto es 'N/A' pero el descuento_promocion global existe
                    elseif (isset($promocion['monto']) && $promocion['monto'] === 'N/A' && !empty($pedido['descuento_promocion']) && $pedido['descuento_promocion'] > 0) {
                        // Aquí asumimos que si hay una sola promoción antigua, su monto es el descuento total de promoción
                        // Si hay varias y es 'N/A', es más complejo saber cuánto contribuyó cada una,
                        // pero si es la única con 'N/A', podemos usar el total.
                        // Para tu caso, donde solo hay una "30 soles por..." y no hay JSON, esto funcionará.
                        $monto_a_mostrar = $pedido['descuento_promocion'];
                    }
                    ?>

                    <?php if ($monto_a_mostrar !== null): ?>
                        <span class="promotion-discount">
                            -S/ <?= is_numeric($monto_a_mostrar) ? number_format((float)$monto_a_mostrar, 2) : htmlspecialchars($monto_a_mostrar) ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

                                    <!-- CUPONES APLICADOS -->
                                    <?php if (!empty($cupones_aplicados)): ?>
                                        <div class="section">
                                            <h3 class="section-title products">Cupones Aplicados</h3>
                                            <div class="coupons-list">
                                                <?php foreach ($cupones_aplicados as $cupon): ?>
                                                    <div class="coupon-item">
                                                        <span class="coupon-code"><?= htmlspecialchars($cupon['codigo']) ?></span>
                                                        <span class="coupon-discount">
                                                            <?php if ($cupon['tipo_descuento'] == 'porcentaje'): ?>
                                                                -<?= $cupon['valor_descuento'] ?>%
                                                            <?php else: ?>
                                                                -S/ <?= number_format($cupon['valor_descuento'], 2) ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Productos del pedido -->
                                    <div class="section">
                                        <h3 class="section-title products">Productos del Pedido</h3>
                                        <table class="products-table">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Variante</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unitario</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $subtotal_total = 0;
                                                foreach ($detalles as $item):
                                                    $subtotal_item = $item['precio_unitario'] * $item['cantidad'];
                                                    $descuento_item = $item['descuento_aplicado'] ?? 0;
                                                    $subtotal_final = $subtotal_item - $descuento_item;
                                                    $subtotal_total += $subtotal_final;
                                                ?>
                                                    <tr>
                                                        <td class="product-name"><?= htmlspecialchars($item['producto_id']) ?></td>
                                                        <td class="product-variant"><?= htmlspecialchars($item['id']) ?></td>
                                                        <td style="text-align: center;">
                                                            <span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: 500;">
                                                                <?= $item['cantidad'] ?>
                                                            </span>
                                                        </td>
                                                        <td class="product-price">S/ <?= number_format($item['precio_unitario'], 2) ?></td>
                                                        <td class="product-discount text-success">
                                                            <?php if ($descuento_item > 0): ?>
                                                                -S/ <?= number_format($descuento_item, 2) ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="product-total">S/ <?= number_format($subtotal_final, 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Panel de gestión -->
                                <div class="management-panel">
                                    <h3 class="management-title">Gestión del Pedido</h3>

                                    <!-- Badge de estado actual -->
                                    <div class="status-badge status-<?= $pedido['estado'] ?>">
                                        <?= ucfirst($pedido['estado']) ?>
                                    </div>

                                    <!-- Cambiar Estado -->
                                    <div class="management-form">
                                        <h4 class="form-title">Cambiar Estado</h4>
                                        <form method="post" action="<?= url('pedido/cambiarEstado') ?>">
                                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                                            <div class="form-group">
                                                <label for="estado" class="form-label">Nuevo estado:</label>
                                                <select name="estado" id="estado" class="form-select">
                                                    <?php foreach ($estados as $estado): ?>
                                                        <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>
                                                            <?= ucfirst($estado) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-submit">
                                                    Actualizar estado
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="management-form">
                                        <h4 class="form-title">Observaciones del Administrador</h4>
                                        <form method="post" action="<?= url('pedido/guardarObservacion') ?>">
                                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                                            <div class="form-group">
                                                <label for="observacion" class="form-label">Observaciones:</label>
                                                <textarea
                                                    name="observacion"
                                                    id="observacion"
                                                    class="form-textarea"
                                                    placeholder="Escribe aquí cualquier observación sobre el pedido..."><?= htmlspecialchars($pedido['observacion'] ?? '') ?></textarea>
                                                <button type="submit" class="btn-submit">
                                                    Guardar observación
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>