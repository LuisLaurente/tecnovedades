<h2>Editar Etiqueta</h2>
<form method="POST" action="">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($etiqueta['nombre']) ?>" required>
    <button type="submit">Actualizar</button>
</form>