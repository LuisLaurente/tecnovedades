<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/cupon.css') ?>">

<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">

            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <!-- Incluir header superior fijo -->
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="historial-container">
                        <a href="<?= url('cupon') ?>" class="back-button">← Volver a Cupones</a>

                        <h1>Historial de Uso - Cupón <?= htmlspecialchars($cupon['codigo']) ?></h1>

                        <div class="cupon-info">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                                <div>
                                    <strong>Código:</strong> <?= htmlspecialchars($cupon['codigo']) ?>
                                </div>
                                <div>
                                    <strong>Tipo:</strong>
                                    <?= $cupon['tipo'] === 'porcentaje' ? $cupon['valor'] . '%' : 'S/. ' . number_format($cupon['valor'], 2) ?>
                                </div>
                                <div>
                                    <strong>Estado:</strong>
                                    <span class="badge <?= $cupon['estado_vigencia'] ?>"><?= ucfirst($cupon['estado_vigencia']) ?></span>
                                </div>
                                <div>
                                    <strong>Período:</strong>
                                    <?= date('d/m/Y', strtotime($cupon['fecha_inicio'])) ?> -
                                    <?= date('d/m/Y', strtotime($cupon['fecha_fin'])) ?>
                                </div>
                                <div>
                                    <strong>Monto Mínimo:</strong>
                                    S/. <?= number_format($cupon['monto_minimo'], 2) ?>
                                </div>
                                <div>
                                    <strong>Límite Global:</strong>
                                    <?= $cupon['limite_uso'] ?: 'Sin límite' ?>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($historial)): ?>
                            <div class="empty-state">
                                <h3>Sin Usos Registrados</h3>
                                <p>Este cupón aún no ha sido utilizado por ningún cliente.</p>
                            </div>
                        <?php else: ?>
                            <div class="historial-table">
                                <table style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Fecha de Uso</th>
                                            <th>Cliente</th>
                                            <th>Correo</th>
                                            <th>Pedido #</th>
                                            <th>Monto Pedido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $uso): ?>
                                            <tr>
                                                <td>
                                                    <?= date('d/m/Y H:i', strtotime($uso['fecha_uso'])) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($uso['nombre_completo'] ?: 'Cliente eliminado') ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($uso['correo'] ?: '-') ?>
                                                </td>
                                                <td>
                                                    <?php if ($uso['pedido_id']): ?>
                                                        <a href="<?= url('pedido/ver?id=' . $uso['pedido_id']) ?>"
                                                            style="color: #007bff; text-decoration: none;">
                                                            #<?= $uso['pedido_id'] ?>
                                                        </a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($uso['monto_total']): ?>
                                                        S/. <?= number_format($uso['monto_total'], 2) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 20px; text-align: center; color: #6c757d;">
                                <strong>Total de usos: <?= count($historial) ?></strong>
                            </div>
                        <?php endif; ?>
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