<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?= $pedido['id'] ?> - TecnoVedades</title>
    <link rel="stylesheet" href="<?= url('css/ver.css') ?>">
</head>
<body>

<?php
$estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
?>

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
                <!-- Información del cliente -->
                <div class="section">
                    <h3 class="section-title client-info">Información del Cliente</h3>
                    <div class="client-card">
                        <div class="client-row">
                            <span class="client-label">Cliente ID:</span>
                            <span class="client-value"><?= htmlspecialchars($pedido['cliente_id']) ?></span>
                        </div>
                        <div class="client-row">
                            <span class="client-label">Fecha del pedido:</span>
                            <span class="client-value"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Detalles del pedido -->
                <div class="section">
                    <h3 class="section-title order-details">Detalles del Pedido</h3>
                    <div class="order-details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Estado</div>
                            <div class="detail-value"><?= ucfirst($pedido['estado']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Monto Total</div>
                            <div class="detail-value total">S/ <?= number_format($pedido['monto_total'], 2) ?></div>
                        </div>
                    </div>
                </div>

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
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $item): ?>
                                <tr>
                                    <td class="product-name"><?= htmlspecialchars($item['producto_id']) ?></td>
                                    <td class="product-variant"><?= htmlspecialchars($item['id']) ?></td>
                                    <td style="text-align: center;">
                                        <span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: 500;">
                                            <?= $item['cantidad'] ?>
                                        </span>
                                    </td>
                                    <td class="product-price">S/ <?= number_format($item['precio_unitario'], 2) ?></td>
                                    <td class="product-total">S/ <?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?></td>
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

</body>
</html>