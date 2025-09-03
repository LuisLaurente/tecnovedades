<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>
                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-6xl mx-auto">
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Reporte de Reseñas</h1>
                            <p class="text-gray-600">Administra las reseñas de los clientes (aprobar o eliminar).</p>
                        </div>

                        <!-- Mensaje de éxito/error -->
                        <?php if (isset($_SESSION['mensaje_review'])): ?>
                            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?= $_SESSION['mensaje_review']; ?></span>
                            </div>
                            <?php unset($_SESSION['mensaje_review']); ?>
                        <?php endif; ?>

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
                                                <th class="px-4 py-2 border">💬 Comentario</th>
                                                <th class="px-4 py-2 border">🕒 Fecha</th>
                                                <th class="px-4 py-2 border text-center">✅ Estado</th>
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
                                                    <td class="px-4 py-2">
                                                        <strong><?= htmlspecialchars($review['titulo'] ?? '') ?></strong>  

                                                        <?= htmlspecialchars($review['texto']) ?>
                                                    </td>
                                                    <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                                                    <td class="px-4 py-2 text-center">
                                                        <!-- Lógica para mostrar el estado actual -->
                                                        <?php if ($review['estado'] === 'aprobado'): ?>
                                                            <span class="inline-block bg-green-200 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">Aprobado</span>
                                                        <?php else: ?>
                                                            <span class="inline-block bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full text-xs font-semibold">Pendiente</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-4 py-2 text-center space-x-2">
                                                        <!-- Lógica para mostrar acciones según el estado -->
                                                        <?php if ($review['estado'] === 'pendiente'): ?>
                                                            <a href="<?= url('review/aprobar/' . $review['id']) ?>" class="inline-block bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Aprobar</a>
                                                        <?php else: ?>
                                                            <a href="<?= url('review/rechazar/' . $review['id']) ?>" class="inline-block bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Rechazar</a>
                                                        <?php endif; ?>
                                                        <a href="<?= url('review/eliminar/' . $review['id']) ?>" class="inline-block bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" onclick="return confirm('¿Seguro que deseas eliminar esta reseña?')">Eliminar</a>
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
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
