<h2>🎉 Configurar Pop-up Promocional</h2>

<form action="<?= url('adminPopup/guardar') ?>" method="post" enctype="multipart/form-data">
    <label>Texto del Pop-up:</label><br>
    <textarea name="texto" rows="4" cols="50"><?= htmlspecialchars($popup['texto'] ?? '') ?></textarea><br><br>

    <label>Imagen actual:</label><br>
    <?php if (!empty($popup['imagen'])): ?>
        <img src="<?= url('images/popup/' . $popup['imagen']) ?>" alt="Imagen actual" style="max-width: 200px;"><br>
        <label><input type="checkbox" name="eliminar_imagen"> Eliminar imagen</label><br><br>
    <?php else: ?>
        <p><em>No hay imagen actual</em></p>
    <?php endif; ?>

    <label>Subir nueva imagen:</label><br>
    <input type="file" name="nueva_imagen" accept="image/*"><br><br>

    <label>
        <input type="checkbox" name="activo" <?= ($popup['activo'] ?? 0) ? 'checked' : '' ?>>
        Activar pop-up
    </label><br><br>

    <button type="submit">Guardar cambios</button>
</form>
