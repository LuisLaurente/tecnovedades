<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>

        <!-- Main -->
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <!-- Contenido -->
                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-6xl mx-auto">

                        <!-- Título -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Reporte de Reseñas</h1>
                            <p class="text-gray-600">Administra las reseñas de los clientes (aprobar o eliminar)</p>
                        </div>

                        <!-- Tabla de reseñas -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <?php if (empty($reviews)): ?>
                                <p class="text-gray-600">No hay reseñas aún.</p>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm text-left border border-gray-300">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 border">📦 Producto</th>
                                                <th class="px-4 py-2 border">👤 Usuario</th>
                                                <th class="px-4 py-2 border">⭐ Puntuación</th>
                                                <th class="px-4 py-2 border">📌 Título</th>
                                                <th class="px-4 py-2 border">💬 Comentario</th>
                                                <th class="px-4 py-2 border">🕒 Fecha</th>
                                                <th class="px-4 py-2 border text-center">⚙️ Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($reviews as $review): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2"><?= htmlspecialchars($review['producto_nombre']) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($review['usuario_nombre']) ?></td>
                                                    <td class="px-4 py-2">
                                                        <?php for ($i=1; $i<=5; $i++): ?>
                                                            <span class="<?= $i <= $review['puntuacion'] ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                                                        <?php endfor; ?>
                                                    </td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($review['titulo'] ?? '') ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($review['texto']) ?></td>
                                                    <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                                                    <td class="px-4 py-2 text-center space-x-2">
                                                        <?php if ($review['estado'] === 'pendiente'): ?>
                                                            <a href="<?= url('review/aprobar/' . $review['id']) ?>" 
                                                               class="inline-block bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Aprobar</a>
                                                        <?php endif; ?>
                                                        <a href="<?= url('review/eliminar/' . $review['id']) ?>" 
                                                           class="inline-block bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                           onclick="return confirm('¿Seguro que deseas eliminar esta reseña?')">Eliminar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

