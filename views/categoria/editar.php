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
                    <h1 class="text-2xl font-semibold mb-4">Editar Categoría</h1>

                    <!-- Mensajes de error -->
                    <?php if (!empty($error)): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errores)): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errores as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('categoria/actualizar') ?>" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id" value="<?= (int)$categoria['id'] ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="2097152"> <!-- 2 MB -->

                        <div>
                            <label for="nombre" class="block font-medium">Nombre de la categoría:</label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>"
                                required
                                class="mt-1 block w-full border rounded px-3 py-2"
                            >
                        </div>

                        <div>
                            <label for="id_padre" class="block font-medium">Categoría padre (opcional):</label>
                            <select name="id_padre" id="id_padre" class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">-- Ninguna (Categoría principal) --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <?php if ($cat['id'] != $categoria['id']): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $categoria['id_padre']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium">Imagen actual</label>
                            <?php if (!empty($categoria['imagen'])): ?>
                                <div class="mt-2">
                                    <img
                                        src="<?= url('uploads/categorias/' . $categoria['imagen']) ?>"
                                        alt="<?= htmlspecialchars($categoria['nombre']) ?>"
                                        style="max-width:200px; border-radius:6px; border:1px solid #e5e7eb;"
                                    >
                                </div>
                            <?php else: ?>
                                <p class="mt-2 text-sm text-gray-600">No hay imagen.</p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="imagen" class="block font-medium">Reemplazar imagen (opcional) — jpg, png, webp, gif. Máx 2 MB</label>
                            <input
                                type="file"
                                name="imagen"
                                id="imagen-edit"
                                accept="image/*"
                                class="mt-2"
                            >
                            <div id="preview-wrapper-edit" class="mt-3" style="display:none;">
                                <div class="text-sm text-gray-600 mb-1">Vista previa nueva imagen:</div>
                                <img id="preview-edit" src="#" alt="Vista previa de la imagen" style="max-width:200px; border-radius:6px; border:1px solid #e5e7eb;">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                Actualizar categoría
                            </button>
                            <a href="<?= url('categoria') ?>" class="text-gray-700 hover:underline">← Volver al listado</a>
                        </div>
                    </form>
                </div>

                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Preview simple para input file (editar)
        (function(){
            const input = document.getElementById('imagen-edit');
            const preview = document.getElementById('preview-edit');
            const wrapper = document.getElementById('preview-wrapper-edit');

            if (!input) return;

            input.addEventListener('change', function(){
                const file = this.files && this.files[0];
                if (!file) {
                    wrapper.style.display = 'none';
                    preview.src = '#';
                    return;
                }

                const maxBytes = 2097152; // 2MB
                if (file.size > maxBytes) {
                    alert('El archivo supera el tamaño máximo de 2 MB.');
                    this.value = '';
                    wrapper.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                    wrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
</body>

</html>
