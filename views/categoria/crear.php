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
                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">

                    <h1>Crear Nueva Categoría</h1>

                    <?php if (!empty($errores)): ?>
                        <div style="color:red;">
                            <ul>
                                <?php foreach ($errores as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('categoria/crear') ?>">
                        <label for="nombre">Nombre de la categoría:</label><br>
                        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre ?? '') ?>" required><br><br>

                        <label for="id_padre">Categoría padre (opcional):</label><br>
                        <select name="id_padre" id="id_padre">
                            <option value="">-- Ninguna (Categoría principal) --</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= isset($id_padre) && $id_padre == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br><br>

                        <button type="submit">Guardar categoría</button>
                    </form>

                    <p><a href="<?= url('categoria') ?>">← Volver al listado</a></p>
                </div>
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>