<?php
// --- Función Auxiliar para describir la promoción ---
// Esta función convierte los datos JSON de la promoción en un texto legible.
if (!function_exists('describirPromocion')) {
    function describirPromocion($condicion, $accion)
    {
        $condicion = json_decode($condicion, true);
        $accion = json_decode($accion, true);

        if (!$condicion || !$accion) {
            return '<span class="text-red-500">Error en datos</span>';
        }

        $tipoCondicion = $condicion['tipo'] ?? 'desconocida';
        $tipoAccion = $accion['tipo'] ?? 'desconocida';

        switch ($tipoCondicion) {
            case 'subtotal_minimo':
                $valorCond = number_format($condicion['valor'] ?? 0, 2);
                if ($tipoAccion === 'descuento_porcentaje') {
                    $valorAcc = $accion['valor'] ?? 0;
                    return "Si el carrito supera S/ {$valorCond} → <strong>{$valorAcc}% de descuento</strong>";
                }
                if ($tipoAccion === 'descuento_fijo') {
                    $valorAcc = number_format($accion['valor'] ?? 0, 2);
                    return "Si el carrito supera S/ {$valorCond} → <strong>S/ {$valorAcc} de descuento</strong>";
                }
                if ($tipoAccion === 'envio_gratis') {
                    return "Si el carrito supera S/ {$valorCond} → <strong>Envío Gratis</strong>";
                }
                break;

            case 'primera_compra':
                return "Si es la primera compra del usuario → <strong>Envío Gratis</strong>";

            case 'cantidad_producto_identico':
                $prodId = $condicion['producto_id'] ?? 'N/A';
                if ($tipoAccion === 'compra_n_paga_m') {
                    $lleva = $accion['cantidad_lleva'] ?? 'N';
                    $paga = $accion['cantidad_paga'] ?? 'M';
                    return "Lleva {$lleva}, Paga {$paga} en Producto #{$prodId}";
                }
                if ($tipoAccion === 'descuento_enesima_unidad') {
                    $unidad = $accion['numero_unidad'] ?? 'N';
                    $desc = $accion['descuento_unidad'] ?? 0;
                    return "<strong>{$desc}% de descuento</strong> en la {$unidad}ª unidad del Producto #{$prodId}";
                }
                break;

            case 'cantidad_producto_categoria':
                if ($tipoAccion === 'descuento_menor_valor') {
                    $catId = $condicion['categoria_id'] ?? 'N/A';
                    $cantidad = $condicion['cantidad_min'] ?? 'N';
                    $desc = $accion['valor'] ?? 0;
                    return "<strong>{$desc}% de descuento</strong> en el producto de menor valor al llevar {$cantidad} de la Categoría #{$catId}";
                }
                break;

            case 'cantidad_total_productos':
                $cantidadMin = $condicion['cantidad_min'] ?? 0;
                if ($tipoAccion === 'compra_n_paga_m_general') {
                    $lleva = $accion['cantidad_lleva'] ?? 'N';
                    $paga = $accion['cantidad_paga'] ?? 'M';
                    return "Al llevar {$cantidadMin} productos mezclados → <strong>Lleva {$lleva}, Paga {$paga} (el de menor valor gratis)</strong>";
                }
                if ($tipoAccion === 'descuento_enesimo_producto') {
                    $numProducto = $accion['numero_producto'] ?? 'N';
                    $descuento = $accion['descuento_porcentaje'] ?? 0;
                    return "Al llevar {$cantidadMin} productos mezclados → <strong>{$descuento}% de descuento en el {$numProducto}º producto (menor valor)</strong>";
                }
                break;
            case 'descuento_producto_mas_barato':
                $cantidadMin = $condicion['cantidad_min'] ?? 0;
                $desc = $accion['valor'] ?? 0;
                return "Al llevar {$cantidadMin} productos mezclados → <strong>{$desc}% de descuento en el producto más barato</strong>";
                break;
        }
        return 'Regla personalizada';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/promocion.css') ?>">

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

                <div class="promociones-page flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="admin-container">
                        <div class="dashboard-header">
                            <h1 class="dashboard-title">Gestión de Promociones</h1>
                            <p class="dashboard-subtitle">Administra cupones, descuentos y ofertas especiales.</p>
                        </div>

                        <!-- Mensajes de sesión -->
                        <?php if (isset($_SESSION['mensaje'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
                            <?php unset($_SESSION['mensaje']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <!-- Panel de estadísticas -->
                        <?php
                        $promocionModel = new \Models\Promocion();
                        $stats = $promocionModel->obtenerEstadisticas();
                        ?>
                        <div class="stats-panel">
                            <div class="stat-card total">
                                <div class="stat-number"><?= $stats['total'] ?></div>
                                <div class="stat-label">Total</div>
                            </div>
                            <div class="stat-card activas">
                                <div class="stat-number"><?= $stats['activas'] ?></div>
                                <div class="stat-label">Activas</div>
                            </div>
                            <div class="stat-card vigentes">
                                <div class="stat-number"><?= $stats['vigentes'] ?></div>
                                <div class="stat-label">Vigentes</div>
                            </div>
                            <div class="stat-card vencidas">
                                <div class="stat-number"><?= $stats['vencidas'] ?></div>
                                <div class="stat-label">Vencidas</div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="<?= url('promocion/crear') ?>" class="btn-primary">Nueva Promoción</a>
                        </div>

                        <?php if (!empty($promociones)): ?>
                            <div class="promotions-panel">
                                <div class="table-header">
                                    <h3 class="table-title">Lista de Promociones</h3>
                                    <span class="promotions-count"><?= count($promociones) ?> promociones</span>
                                </div>
                                <div class="table-container">
                                    <table class="promotions-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Regla de Promoción</th>
                                                <th>Estado</th>
                                                <th>Vigencia</th>
                                                <th>Prioridad</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($promociones as $promocion): ?>
                                                <?php
                                                $ahora = date('Y-m-d');
                                                $esVigente = $ahora >= $promocion['fecha_inicio'] && $ahora <= $promocion['fecha_fin'];
                                                ?>
                                                <tr>
                                                    <td class="promotion-name">
                                                        <?= htmlspecialchars($promocion['nombre']) ?>
                                                        <?php if ($promocion['exclusivo']): ?>
                                                            <span class="exclusive-tag">EXCLUSIVO</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= describirPromocion($promocion['condicion'], $promocion['accion']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?= $promocion['activo'] ? 'activo' : 'inactivo' ?>">
                                                            <?= $promocion['activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                        <?php if ($promocion['activo']): ?>
                                                            <span class="status-badge status-<?= $esVigente ? 'vigente' : 'vencido' ?>">
                                                                <?= $esVigente ? 'Vigente' : ($ahora > $promocion['fecha_fin'] ? 'Expirado' : 'Programado') ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="date-range">
                                                            <strong>Inicio:</strong> <?= date('d/m/Y', strtotime($promocion['fecha_inicio'])) ?>
                                                            <strong>Fin:</strong> <?= date('d/m/Y', strtotime($promocion['fecha_fin'])) ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="priority-badge"><?= $promocion['prioridad'] ?></span>
                                                    </td>
                                                    <td class="actions-cell">
                                                        <a href="<?= url('promocion/editar/' . $promocion['id']) ?>" class="btn-action btn-edit">Editar</a>
                                                        <a href="<?= url('promocion/toggleEstado/' . $promocion['id']) ?>" class="btn-action btn-toggle">
                                                            <?= $promocion['activo'] ? 'Desactivar' : 'Activar' ?>
                                                        </a>
                                                        <form action="<?= url('promocion/eliminar/' . $promocion['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta promoción? No se puede deshacer.')" style="display:inline;">
                                                            <button type="submit" class="btn-action btn-delete">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="promotions-panel">
                                <div class="empty-state">
                                    <h3>No hay promociones registradas</h3>
                                    <p>Crea tu primera promoción para empezar a ofrecer descuentos y ofertas especiales.</p>
                                    <div style="margin-top: 20px;">
                                        <a href="<?= url('promocion/crear') ?>" class="btn-primary">Crear Primera Promoción</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
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