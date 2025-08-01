<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Categoría</title>
</head>

<body>
    <h1>Editar Categoría</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>


    <form method="POST" action="<?= url('categoria/actualizar') ?>">
        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">

        <label for="nombre">Nombre de la categoría:</label><br>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required><br><br>

        <label for="id_padre">Categoría padre (opcional):</label><br>
        <select name="id_padre" id="id_padre">
            <option value="">-- Ninguna (Categoría principal) --</option>
            <?php foreach ($categorias as $cat): ?>
                <?php if ($cat['id'] != $categoria['id']): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $categoria['id_padre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Actualizar categoría</button>
    </form>

    <p><a href="<?= url('categoria') ?>">← Volver al listado</a></p>
</body>

</html>