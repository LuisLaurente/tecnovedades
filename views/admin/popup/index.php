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
                                <label class="block font-semibold text-gray-700 mb-2">📝 Texto del Pop-up:</label>
                                <textarea name="texto" rows="4" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($popup['texto'] ?? '') ?></textarea>
                            </div>

                            <!-- Subir nuevas imágenes -->
                            <div>
                                <label class="block font-semibold text-gray-700 mb-2">📁 Subir nuevas imágenes:</label>
                                <input type="file" name="nuevas_imagenes[]" accept="image/*" multiple class="w-full">
                            </div>

                            <!-- Activar popup -->
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="activo" <?= ($popup['activo'] ?? 0) ? 'checked' : '' ?> class="w-5 h-5">
                                <label class="text-gray-700 font-medium">Activar pop-up</label>
                            </div>

                            <!-- Imágenes disponibles -->
                            <?php if (!empty($imagenes)): ?>
                                <div>
                                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">📸 Imágenes disponibles</h2>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                        <?php foreach ($imagenes as $img): ?>
                                            <div class="border rounded-lg p-3 text-center shadow-sm">
                                                <img src="<?= url('images/popup/' . htmlspecialchars($img['nombre_imagen'])) ?>" 
                                                    class="w-full h-32 object-contain rounded mb-2" 
                                                    alt="Imagen">
                                                
                                                <div class="flex items-center justify-center mb-2">
                                                    <input type="radio" name="imagen_principal" value="<?= htmlspecialchars($img['nombre_imagen']) ?>"
                                                        <?= ($popup['imagen'] == $img['nombre_imagen']) ? 'checked' : '' ?> class="mr-2">
                                                    <label class="text-sm text-gray-700">Principal</label>
                                                </div>

                                                <a href="<?= url('adminPopup/eliminarImagen/' . $img['id']) ?>"
                                                onclick="return confirm('¿Eliminar esta imagen?')"
                                                class="text-red-600 hover:underline text-sm">❌ Eliminar</a>
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
