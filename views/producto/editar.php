<h1>Editar Producto</h1>

<form action="/producto/actualizar/<?= $producto['id'] ?>" method="POST">
    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br>

    <label>Descripción:</label>
    <textarea name="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea><br>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required><br>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?= htmlspecialchars($producto['stock']) ?>" required><br>

    <label>Visible:</label>
    <select name="visible">
        <option value="1" <?= $producto['visible'] ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= !$producto['visible'] ? 'selected' : '' ?>>No</option>
    </select><br><br>

        <label>Etiquetas:</label>
    <select name="etiquetas[]" multiple>
    <?php foreach ($etiquetas as $et): ?>
    <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
        <?= htmlspecialchars($et['nombre']) ?>
    </option>
    <?php endforeach; ?>
</select>

    <button type="submit">Actualizar</button>
</form>


<h4>Etiquetas asignadas (debug):</h4>
<ul>
<?php foreach ($etiquetasAsignadas as $id_et): ?>
  <li>ID etiqueta: <?= $id_et ?></li>
<?php endforeach; ?>
</ul>


<h2>Variantes del Producto</h2>

<?php if (!empty($variantes)): ?>
    <?php foreach ($variantes as $variante): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <form action="/variante/actualizar/<?= $variante['id'] ?>" method="POST">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

                <label>Talla:</label>
                <input type="text" name="talla" value="<?= htmlspecialchars($variante['talla']) ?>" required>

                <label>Color:</label>
                <input type="text" name="color" value="<?= htmlspecialchars($variante['color']) ?>" required>

                <label>Stock:</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($variante['stock']) ?>" required>

                <button type="submit">Actualizar Variante</button>
                <a href="/variante/eliminar/<?= $variante['id'] ?>?producto_id=<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta variante?')">❌ Eliminar</a>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay variantes registradas.</p>
<?php endif; ?>
