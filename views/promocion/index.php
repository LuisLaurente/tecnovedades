<?php
// --- Función Auxiliar para describir la promoción ---
if (!function_exists('describirPromocion')) {
    function describirPromocion($condicion, $accion, $tipo = null)
    {
        $condicion = json_decode($condicion, true);
        $accion = json_decode($accion, true);

        if (!$condicion || !$accion || empty($condicion['tipo']) || empty($accion['tipo'])) {
            error_log("Error en describirPromocion: Condicion=" . json_encode($condicion) . ", Accion=" . json_encode($accion));
            return '<span class="error-text">Error en datos</span>';
        }

        $tipoCondicion = $condicion['tipo'];
        $tipoAccion = $accion['tipo'];

        if ($tipo && $tipo !== $tipoCondicion) {
            error_log("Inconsistencia en tipo: DB=$tipo, Condicion=$tipoCondicion");
        }

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
                if ($tipoAccion === 'envio_gratis') {
                    return "Si es la primera compra del usuario → <strong>Envío Gratis</strong>";
                }
                break;

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
                    $descuento = $accion['valor'] ?? 0;
                    return "Al llevar {$cantidadMin} productos mezclados → <strong>{$descuento}% de descuento en el producto más barato</strong>";
                }
                break;

            case 'todos':
                if ($tipoAccion === 'envio_gratis') {
                    return "<strong>Envío Gratis</strong> para cualquier pedido";
                }
                break;

            default:
                error_log("Tipo de condición desconocido: $tipoCondicion, Accion: $tipoAccion");
                return 'Regla personalizada';
        }
        error_log("Combinación no soportada: Condicion=$tipoCondicion, Accion=$tipoAccion");
        return 'Regla personalizada';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/promocion.css') ?>">

<body>
    <div class="admin-layout">
        <div class="sidebar-container">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        
        <div class="main-content">
            <div class="header-container">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </div>

            <div class="content-wrapper">
                <div class="promociones-container">
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
                                                <td class="promotion-rule">
                                                    <?= describirPromocion($promocion['condicion'], $promocion['accion']) ?>
                                                </td>
                                                <td class="status-cell">
                                                    <span class="status-badge status-<?= $promocion['activo'] ? 'activo' : 'inactivo' ?>">
                                                        <?= $promocion['activo'] ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                    <?php if ($promocion['activo']): ?>
                                                        <span class="status-badge status-<?= $esVigente ? 'vigente' : 'vencido' ?>">
                                                            <?= $esVigente ? 'Vigente' : ($ahora > $promocion['fecha_fin'] ? 'Expirado' : 'Programado') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="date-cell">
                                                    <div class="date-range">
                                                        <div><strong>Inicio:</strong> <?= date('d/m/Y', strtotime($promocion['fecha_inicio'])) ?></div>
                                                        <div><strong>Fin:</strong> <?= date('d/m/Y', strtotime($promocion['fecha_fin'])) ?></div>
                                                    </div>
                                                </td>
                                                <td class="priority-cell">
                                                    <span class="priority-badge"><?= $promocion['prioridad'] ?></span>
                                                </td>
                                                <td class="actions-cell">
                                                    <a href="<?= url('promocion/editar/' . $promocion['id']) ?>" class="btn-action btn-edit">Editar</a>
                                                    <a href="<?= url('promocion/toggleEstado/' . $promocion['id']) ?>" class="btn-action btn-toggle">
                                                        <?= $promocion['activo'] ? 'Desactivar' : 'Activar' ?>
                                                    </a>
                                                    <form action="<?= url('promocion/eliminar/' . $promocion['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta promoción? No se puede deshacer.')">
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
                                <div class="empty-action">
                                    <a href="<?= url('promocion/crear') ?>" class="btn-primary">Crear Primera Promoción</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>