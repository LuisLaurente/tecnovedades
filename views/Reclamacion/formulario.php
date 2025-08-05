<link rel="stylesheet" href="<?= url('css/reclamForm.css') ?>">
<script src="<?= url('js/reclamForm.js') ?>"></script>
<div id="toast" class="toast-exito" style="display: none;">
  ✅ Su reclamo se ha enviado con éxito.
</div>


<?php if (!empty($mensaje_exito)): ?>
    <p style="color: green;"><?= htmlspecialchars($mensaje_exito) ?></p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul style="color: red;">
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>📋 Libro de Reclamaciones</h2>

<form method="post" action="<?= url('reclamacion/enviar') ?>">
    <label>Nombre completo:*<br>
        <input type="text" name="nombre" required>
    </label><br><br>

    <label>Correo electrónico:*<br>
        <input type="email" name="correo" required>
    </label><br><br>

    <label>Teléfono:<br>
        <input type="tel" name="telefono">
    </label><br><br>

    <label>Mensaje:*<br>
        <textarea name="mensaje" rows="5" required></textarea>
    </label><br><br>

    <button type="submit">Enviar Reclamación</button>
</form>

<!-- 🔙 Botón para volver a la vista de reclamaciones recibidas -->
<br>
<a class="volver" href="<?= url('adminReclamacion/index') ?>">
    ← Volver a Reclamaciones Recibidas
</a>