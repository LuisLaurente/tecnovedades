<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
        </div>

        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <!-- Contenido principal -->
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../includes/header.php'; ?>
                </div>

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6">🎉 Configurar Pop-up Promocional</h1>

                <form action="<?= url('adminPopup/guardar') ?>" method="post" enctype="multipart/form-data" class="space-y-6">

                <!-- Texto -->
                <div>
                    <label for="texto" class="block font-semibold text-gray-700 mb-2">📝 Texto del Pop-up</label>
                    <textarea id="texto" name="texto" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y"
                    placeholder="Escribe el texto que quieres mostrar en el pop-up..."><?= htmlspecialchars($popup['texto'] ?? '') ?></textarea>
                </div>

                <!-- Subir nuevas imágenes -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-2">📁 Subir nuevas imágenes</label>

                    <div class="flex items-start gap-4">
                    <!-- botón compacto -->
                    <label for="nuevas_imagenes" class="inline-flex items-center justify-center w-40 px-4 py-2 bg-indigo-600 text-white rounded-lg cursor-pointer hover:bg-indigo-700 transition text-sm">
                        📤 Seleccionar
                    </label>

                    <!-- input oculto (usar label para activar) -->
                    <input id="nuevas_imagenes" name="nuevas_imagenes[]" type="file" accept="image/*" multiple class="sr-only">

                    <!-- texto con nombre o estado -->
                    <div class="flex-1">
                        <p id="nuevasInfo" class="text-sm text-gray-600">Ningún archivo seleccionado</p>
                        <p class="text-xs text-gray-500 mt-1">Puedes seleccionar varias imágenes. Se mostrarán como miniaturas abajo. Para finalizar presione GUARDAR CAMBIOS</p>

                        <!-- Previsualización de nuevos archivos -->
                        <div id="previewNuevas" class="mt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3"></div>
                    </div>
                    </div>
                </div>

                <!-- Activar popup -->
                <div class="flex items-center gap-3">
                    <input id="activo" type="checkbox" name="activo" <?= ($popup['activo'] ?? 0) ? 'checked' : '' ?>
                        class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                    <label for="activo" class="text-gray-700 font-medium">Activar pop-up</label>
                </div>

                <!-- Imágenes disponibles -->
                <?php if (!empty($imagenes)): ?>
                    <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">📸 Imágenes disponibles</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                        <?php foreach ($imagenes as $img): 
                            $imgName = htmlspecialchars($img['nombre_imagen']);
                            $imgId = (int)$img['id'];
                            $isChecked = (isset($popup['imagen']) && $popup['imagen'] === $img['nombre_imagen']);
                        ?>
                        <div class="border rounded-lg p-3 text-center shadow-sm bg-white">
                            <div class="mb-3">
                            <img src="<?= url('images/popup/' . $imgName) ?>"
                                alt="Imagen <?= $imgId ?>"
                                class="w-full h-32 object-contain rounded">
                            </div>

                            <div class="flex items-center justify-center mb-3">
                            <input id="principal_<?= $imgId ?>" type="radio" name="imagen_principal" value="<?= $imgName ?>"
                                    <?= $isChecked ? 'checked' : '' ?>
                                    class="mr-2 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <label for="principal_<?= $imgId ?>" class="text-sm text-gray-700">Principal</label>
                            </div>

                            <a href="<?= url('adminPopup/eliminarImagen/' . $imgId) ?>"
                            onclick="return confirm('¿Eliminar esta imagen?')"
                            class="inline-block px-3 py-1 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                            ❌ Eliminar
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic">No hay imágenes subidas.</p>
                <?php endif; ?>

                <!-- Botón guardar -->
                <div class="text-right">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                    💾 Guardar cambios
                    </button>
                </div>
                </form>

                <!-- Script: previsualización ligera y actualización de texto -->
                <script>
                (function(){
                    const input = document.getElementById('nuevas_imagenes');
                    const info = document.getElementById('nuevasInfo');
                    const preview = document.getElementById('previewNuevas');

                    if (!input || !info || !preview) return;

                    input.addEventListener('change', () => {
                    preview.innerHTML = '';
                    const files = Array.from(input.files || []);
                    if (files.length === 0) {
                        info.textContent = 'Ningún archivo seleccionado';
                        return;
                    }

                    info.textContent = files.length + (files.length === 1 ? ' archivo seleccionado' : ' archivos seleccionados');

                    files.slice(0, 12).forEach(file => {
                        if (!file.type.startsWith('image/')) return;

                        const reader = new FileReader();
                        const container = document.createElement('div');
                        container.className = 'w-full h-24 bg-gray-50 rounded overflow-hidden flex items-center justify-center';

                        reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = file.name;
                        img.className = 'max-h-24 object-contain';
                        container.appendChild(img);
                        };
                        reader.readAsDataURL(file);

                        preview.appendChild(container);
                    });
                    });
                })();
                </script>

                    </div>
                </div>

                <div class="mt-4">
                    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
