<h2>📂 Reclamaciones Recibidas</h2>

<!-- Botón para ir al formulario de reclamos -->
<a href="<?= url('reclamacion/formulario') ?>" style="display: inline-block; margin-bottom: 15px; padding: 8px 16px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">
    ➕ Generar nuevo reclamo
</a>

<table border="1" cellpadding="8">
    <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Mensaje</th>
        <th>Fecha</th>
        <th>Eliminar</th>
    </tr>
    <?php foreach ($reclamaciones as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['nombre']) ?></td>
            <td><?= htmlspecialchars($r['correo']) ?></td>
            <td><?= htmlspecialchars($r['telefono']) ?></td>
            <td><?= nl2br(htmlspecialchars($r['mensaje'])) ?></td>
            <td><?= $r['creado_en'] ?></td>
            <td>
                <a href="<?= url('adminReclamacion/eliminar/' . $r['id']) ?>"
                   onclick="return confirm('¿Estás seguro de eliminar esta reclamación?')">🗑️</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
