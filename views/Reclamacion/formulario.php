<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/reclamForm.css') ?>">
<script src="<?= url('js/reclamForm.js') ?>"></script>
<body>
    <div class="flex h-screen">
        

        <div class="flex-1 ">
            <!-- Contenido principal -->
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6">📋 Libro de Reclamaciones</h1>

                        <!-- Toast de éxito (se mostrará con JS si hay mensaje) -->
                        <?php if (!empty($toast_exito)): ?>
                        <div id="toast" class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4 border border-green-300 toast-exito" style="display: none;">
                            ✅ Reclamo enviado con éxito.
                        </div>
                        <?php endif; ?>
                       

                        
                        <?php if (!empty($advertencia)): ?>
                                <p style="color: orange; font-weight: bold;"><?= htmlspecialchars($advertencia) ?></p>
                            <?php endif; ?>

                        <!-- Mensaje de éxito -->
                        <?php if (!empty($mensaje_exito)): ?>
                            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4 border border-green-300">
                                <?= htmlspecialchars($mensaje_exito) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Errores -->
                        <?php if (!empty($errores)): ?>
                            <ul class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4 border border-red-300 list-disc list-inside">
                                <?php foreach ($errores as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form method="post" action="<?= url('reclamacion/enviar') ?>" class="space-y-4">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Nombre completo: *</label>
                                <input type="text" name="nombre" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Correo electrónico: *</label>
                                <input type="email" name="correo" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Teléfono:</label>
                                <input type="tel" name="telefono" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Código del Pedido:</label>
                                <input type="number" name="pedido_id" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Mensaje: *</label>
                                <textarea name="mensaje" rows="5" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>

                            <div class="flex justify-between items-center mt-6">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                                    Enviar Reclamación
                                </button>

                                <a href="javascript:history.back()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded font-semibold">
                                    ← Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= url('js/reclamForm.js') ?>"></script>
</body>
</html>
