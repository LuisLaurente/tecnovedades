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
            <!-- Main -->
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../includes/header.php'; ?>
                </div>

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-6xl mx-auto">

                        <!-- Título -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-800 mb-2">📂 Reclamaciones Recibidas</h1>
                                    <p class="text-gray-600">Visualiza y gestiona los reclamos enviados por los clientes</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de reclamaciones -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <?php if (!empty($reclamaciones)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm text-left border border-gray-300">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 border">📝 Codigo de pedido</th>
                                                <th class="px-4 py-2 border">👤 Nombre</th>
                                                <th class="px-4 py-2 border">📧 Correo</th>
                                                <th class="px-4 py-2 border">📞 Teléfono</th>
                                                <th class="px-4 py-2 border">📝 Mensaje</th>
                                                <th class="px-4 py-2 border">🕒 Fecha</th>
                                                <th class="px-4 py-2 border">🗑️</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reclamaciones as $r): ?>
                                                <tr class="border-t hover:bg-gray-50">
                                                    <td class="px-4 py-2"><?= htmlspecialchars($r['pedido_id'] ?? 'N/A') ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($r['nombre']) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($r['correo']) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($r['telefono']) ?></td>
                                                    <td class="px-4 py-2 whitespace-pre-line"><?= nl2br(htmlspecialchars($r['mensaje'])) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($r['creado_en']) ?></td>
                                                    <td class="px-4 py-2 text-center">
                                                        <a href="<?= url('adminReclamacion/eliminar/' . $r['id']) ?>"
                                                           onclick="return confirm('¿Estás seguro de eliminar esta reclamación?')"
                                                           class="text-red-600 hover:text-red-800 text-lg">❌</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-600">No hay reclamaciones registradas aún.</p>
                            <?php endif; ?>
                        </div>

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
