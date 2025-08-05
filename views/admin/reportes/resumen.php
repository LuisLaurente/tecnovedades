<h2>📈 Reporte General de Ventas</h2>
<form method="get">
    <label>Desde: <input type="date" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>"></label>
    <label>Hasta: <input type="date" name="fin" value="<?= htmlspecialchars($fechaFin) ?>"></label>
    <button type="submit">Buscar</button>
</form>

<?php if ($resumen): ?>
    <ul>
        <li>🧾 Total vendido: <strong>S/ <?= number_format($resumen['total_vendido'], 2) ?></strong></li>
        <li>📦 Total de pedidos: <strong><?= $resumen['total_pedidos'] ?></strong></li>
        <li>🎟️ Ticket promedio: <strong>S/ <?= number_format($resumen['ticket_promedio'], 2) ?></strong></li>
    </ul>
<?php else: ?>
    <p>No hay datos disponibles para este rango.</p>
<?php endif; ?>

<h2>📦 Reporte Detallado por Producto</h2>

<?php if (!empty($detalles)): ?>
    <table border="1" cellpadding="8" style="margin-top: 15px;">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio Unitario (S/)</th>
                <th>Cantidad Vendida</th>
                <th>Subtotal (S/)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                    <td>S/ <?= number_format($d['precio_unitario'], 2) ?></td>
                    <td><?= $d['cantidad_total'] ?></td>
                    <td>S/ <?= number_format($d['precio_unitario'] * $d['cantidad_total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="margin-top: 20px;">No hay ventas registradas en el rango seleccionado.</p>
<?php endif; ?>

<!-- Botones de exportación -->
<form method="get" action="<?= url('reporte/exportar_csv') ?>" style="margin-top: 20px;">
    <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
    <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin) ?>">
    <input type="hidden" name="tipo" value="general">
    <button type="submit">⬇️ Exportar Reporte General (CSV)</button>
</form>

<form method="get" action="<?= url('reporte/exportar_csv') ?>" style="margin-top: 10px;">
    <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
    <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin) ?>">
    <input type="hidden" name="tipo" value="detalle">
    <button type="submit">⬇️ Exportar Reporte Detallado (CSV)</button>
</form>
