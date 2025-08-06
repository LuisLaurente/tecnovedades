<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Navegación lateral -->
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
                    <div class="max-w-6xl mx-auto">
                        
                        <!-- Título y descripción -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Reporte de Ventas</h1>
                            <p class="text-gray-600">Consulta general y detallada de las ventas realizadas por rango de fechas</p>
                        </div>

                        <!-- Filtro de fechas -->
                        <form method="get" class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <div class="flex items-center gap-4">
                                <label class="flex flex-col">
                                    Desde:
                                    <input type="date" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>" class="input input-bordered border border-gray-300 rounded px-3 py-2">
                                </label>
                                <label class="flex flex-col">
                                    Hasta:
                                    <input type="date" name="fin" value="<?= htmlspecialchars($fechaFin) ?>" class="input input-bordered border border-gray-300 rounded px-3 py-2">
                                </label>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg mt-5">
                                    Buscar
                                </button>
                            </div>
                        </form>
                        <!-- Botones -->
                        <div class="mb-4 flex gap-3">
                            <!-- Botón Reporte General -->
                            <form method="get" action="<?= url('reporte/exportar_csv') ?>">
                                <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
                                <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin) ?>">
                                <input type="hidden" name="tipo" value="general">
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer">
                                    ⬇️ Exportar Reporte General (CSV)
                                </button>
                            </form>

                            <!-- Botón Reporte Detallado -->
                            <form method="get" action="<?= url('reporte/exportar_csv') ?>">
                                <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
                                <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin) ?>">
                                <input type="hidden" name="tipo" value="detalle">
                                <button type="submit" 
                                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg cursor-pointer">
                                    ⬇️ Exportar Reporte Detallado (CSV)
                                </button>
                            </form>
                        </div>


                        <!-- Reporte General -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-2xl font-bold mb-4">📈 Resumen General</h2>
                            <?php if ($resumen): ?>
                                <ul class="space-y-2 text-gray-800">
                                    <li>🧾 Total vendido: 
                                    <strong>S/ <?= number_format($resumen['total_vendido'] ?? 0, 2) ?></strong>
                                    </li>
                                    <li>📦 Total de pedidos: <strong><?= $resumen['total_pedidos'] ?></strong></li>
                                    <li>🎟️ Ticket promedio: 
                                    <strong>S/ <?= number_format($resumen['ticket_promedio'] ?? 0, 2) ?></strong>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <p class="text-gray-600">No hay datos disponibles para este rango.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Reporte Detallado -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold mb-4">📦 Detalle por Producto</h2>
                            <?php if (!empty($detalles)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-sm border">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 border">Producto</th>
                                                <th class="px-4 py-2 border">Precio Unitario (S/)</th>
                                                <th class="px-4 py-2 border">Cantidad Vendida</th>
                                                <th class="px-4 py-2 border">Subtotal (S/)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detalles as $d): ?>
                                            <tr class="border-t">
                                                    <td class="px-4 py-2"><?= htmlspecialchars($d['nombre']) ?></td>
                                                    <td class="px-4 py-2">S/ <?= number_format($d['precio_unitario'], 2) ?></td>
                                                    <td class="px-4 py-2"><?= $d['cantidad_total'] ?></td>
                                                    <td class="px-4 py-2">S/ <?= number_format($d['precio_unitario'] * $d['cantidad_total'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-600 mt-2">No hay ventas registradas en el rango seleccionado.</p>
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
