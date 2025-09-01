<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body class="bg-gray-100">
    <div class="container mx-auto py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">📊 Reporte de Reseñas</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <?php if (empty($reviews)): ?>
                <p class="text-gray-600">No hay reseñas aún.</p>
            <?php else: ?>
                <table class="min-w-full table-auto border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 border">Producto</th>
                            <th class="px-4 py-2 border">Usuario</th>
                            <th class="px-4 py-2 border">Puntuación</th>
                            <th class="px-4 py-2 border">Título</th>
                            <th class="px-4 py-2 border">Comentario</th>
                            <th class="px-4 py-2 border">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($review['producto_nombre']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($review['usuario_nombre']) ?></td>
                                <td class="px-4 py-2 border">
                                    <?php for ($i=1; $i<=5; $i++): ?>
                                        <span class="<?= $i <= $review['puntuacion'] ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                                    <?php endfor; ?>
                                </td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($review['titulo'] ?? '') ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($review['texto']) ?></td>
                                <td class="px-4 py-2 border"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
