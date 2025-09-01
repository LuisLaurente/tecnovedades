<h1>Agregar Review</h1>

<form action="<?= url('review/guardar') ?>" method="post">
    <label for="producto_id">Producto</label>
    <select name="producto_id" id="producto_id" required>
        <option value="">-- Selecciona un producto --</option>
        <?php foreach ($productos as $producto): ?>
            <option value="<?= $producto['id'] ?>">
                <?= htmlspecialchars($producto['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="usuario">Tu nombre</label>
    <input type="text" name="usuario" id="usuario" required>

    <label for="comentario">Comentario</label>
    <textarea name="comentario" id="comentario" required></textarea>

    <label for="puntuacion">Puntuación</label>
    <select name="puntuacion" id="puntuacion" required>
        <option value="1">1 - Muy malo</option>
        <option value="2">2 - Malo</option>
        <option value="3">3 - Regular</option>
        <option value="4">4 - Bueno</option>
        <option value="5">5 - Excelente</option>
    </select>

    <button type="submit">Guardar</button>
</form>
