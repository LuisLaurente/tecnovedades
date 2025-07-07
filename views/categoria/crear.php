<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Categoría</title>
</head>

<body>
    <h1>Crear Nueva Categoría</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/categoria/guardar">
        <label for="nombre">Nombre de la categoría:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>

        <label for="id_padre">Categoría padre (opcional):</label><br>
        <select name="id_padre" id="id_padre">
            <option value="">-- Ninguna (Categoría principal) --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Guardar categoría</button>
    </form>

    <p><a href="/categoria">← Volver al listado</a></p>
</body>

</html>