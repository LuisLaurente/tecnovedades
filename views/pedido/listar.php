<?php
$estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
$estadoFiltro = $_GET['estado'] ?? '';
?>
<h2>Pedidos</h2>
<form method="get" style="margin-bottom:20px;">
    <label>Filtrar por estado:
        <select name="estado">
            <option value="">-- Todos --</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?= $estado ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filtrar</button>
</form>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
    <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Estado</th>
        <th>Monto total</th>
        <th>Fecha</th>
        <th>Observaciones</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($pedidos as $pedido): ?>
        <?php if ($estadoFiltro && $pedido['estado'] !== $estadoFiltro) continue; ?>
        <tr>
            <td><?= $pedido['id'] ?></td>
            <td><?= htmlspecialchars($pedido['cliente_id']) ?></td>
            <td><?= ucfirst($pedido['estado']) ?></td>
            <td>S/ <?= number_format($pedido['monto_total'], 2) ?></td>
            <td><?= $pedido['creado_en'] ?></td>
            <td><?= htmlspecialchars($pedido['observaciones_admin'] ?? '') ?></td>
            <td>
                <a href="/TECNOVEDADES/public/pedido/ver/<?= $pedido['id'] ?>">Ver detalle</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
