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
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                <label class="flex flex-col text-sm">
                                    <span class="text-gray-700">Desde</span>
                                    <input type="date" name="inicio" value="<?= htmlspecialchars($fechaInicio ?? '') ?>" class="border border-gray-300 rounded px-3 py-2 mt-1">
                                </label>
                                <label class="flex flex-col text-sm">
                                    <span class="text-gray-700">Hasta</span>
                                    <input type="date" name="fin" value="<?= htmlspecialchars($fechaFin ?? '') ?>" class="border border-gray-300 rounded px-3 py-2 mt-1">
                                </label>
                                <div class="mt-3 md:mt-0">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg cursor-pointer">
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Botón de exportación completo -->
                        <div class="mb-4">
                            <form method="get" action="<?= url('reporte/exportarCsv') ?>">
                                <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                                <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg cursor-pointer">
                                    ⬇️ Exportar Reporte (CSV)
                                </button>
                            </form>
                        </div>

                        <!-- Resumen General -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-2xl font-bold mb-4">📈 Resumen General</h2>
                            <?php if (!empty($resumen)): ?>
                                <ul class="space-y-2 text-gray-800">
                                    <li>🧾 <strong>Total vendido:</strong> S/ <?= number_format($resumen['total_vendido'] ?? 0, 2) ?></li>
                                    <li>📦 <strong>Total de pedidos:</strong> <?= intval($resumen['total_pedidos'] ?? 0) ?></li>
                                    <li>🎟️ <strong>Ticket promedio:</strong> S/ <?= number_format($resumen['ticket_promedio'] ?? 0, 2) ?></li>
                                    <li>📅 <strong>Rango:</strong> <?= htmlspecialchars($fechaInicio ?? '-') ?> — <?= htmlspecialchars($fechaFin ?? '-') ?></li>
                                </ul>
                            <?php else: ?>
                                <p class="text-gray-600">No hay datos disponibles para este rango.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Detalle por Producto con columnas adicionales de Pedidos -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold mb-4">📦 Detalle por Producto</h2>
                            <?php if (!empty($detalles)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-sm border">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 border">Pedido ID</th>
                                                <th class="px-4 py-2 border">Fecha</th>
                                                <th class="px-4 py-2 border">Producto</th>
                                                <th class="px-4 py-2 border">Precio Unitario (S/)</th>
                                                <th class="px-4 py-2 border">Cantidad Vendida</th>
                                                <th class="px-4 py-2 border">Total (S/)</th>
                                                <th class="px-4 py-2 border text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detalles as $d): ?>
                                                <?php
                                                    $precio = floatval($d['precio_unitario'] ?? $d['precio'] ?? 0);
                                                    $cantidad = intval($d['cantidad_total'] ?? $d['cantidad'] ?? 0);
                                                    $subtotal = $precio * $cantidad;
                                                    $pedidoId = $d['id'] ?? $d['pedido_id'] ?? '';
                                                    $fechaPedido = $d['creado_en'] ?? $d['fecha'] ?? '';
                                                ?>
                                                <tr class="border-t hover:bg-gray-50">
                                                    <td class="px-4 py-2"><?= htmlspecialchars($pedidoId) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($fechaPedido))) ?></td>
                                                    <td class="px-4 py-2"><?= htmlspecialchars($d['nombre'] ?? $d['producto'] ?? '') ?></td>
                                                    <td class="px-4 py-2">S/ <?= number_format($precio, 2) ?></td>
                                                    <td class="px-4 py-2"><?= $cantidad ?></td>
                                                    <td class="px-4 py-2">S/ <?= number_format($subtotal, 2) ?></td>
                                                    <td class="px-4 py-2 text-center">
                                                        <a href="<?= url('pedido/ver/' . ($d['pedido_id'] ?? $d['id'] ?? '')) ?>" class="text-blue-600 hover:text-blue-800">Ver</a>
                                                    </td>
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
