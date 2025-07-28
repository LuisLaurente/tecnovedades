<?php
$estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
?>
<h2>Detalle del Pedido #<?= $pedido['id'] ?></h2>
<p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente_id']) ?></p>
<p><strong>Estado:</strong> <?= ucfirst($pedido['estado']) ?></p>
<p><strong>Monto total:</strong> S/ <?= number_format($pedido['monto_total'], 2) ?></p>
<p><strong>Fecha:</strong> <?= $pedido['creado_en'] ?></p>

<h3>Productos</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Producto</th>
        <th>Variante</th>
        <th>Cantidad</th>
        <th>Precio unitario</th>
        <th>Subtotal</th>
    </tr>
    <?php foreach ($detalles as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['producto_id']) ?></td>
            <td><?= htmlspecialchars($item['id']) ?></td>
            <td><?= $item['cantidad'] ?></td>
            <td>S/ <?= number_format($item['precio_unitario'], 2) ?></td>
            <td>S/ <?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Cambiar estado del pedido</h3>
<form method="post" action="/tecnovedades/public/pedido/cambiarEstado">
    <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
    <select name="estado">
        <?php foreach ($estados as $estado): ?>
            <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Actualizar estado</button>
</form>

<h3>Observaciones del administrador</h3>
<form method="post" action="/tecnovedades/public/pedido/guardarObservacion">
    <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
    <textarea name="observacion" rows="3" cols="50"><?= htmlspecialchars($pedido['observacion'] ?? '') ?></textarea><br>
    <button type="submit">Guardar observación</button>
</form>
<a href="/pedido/listar">&larr; Volver al listado</a>
