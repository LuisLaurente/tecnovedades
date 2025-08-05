<!DOCTYPE html>
<html lang="es">

<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>


<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>
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
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>