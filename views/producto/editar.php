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

    <button type="submit">Actualizar</button>
</form>
