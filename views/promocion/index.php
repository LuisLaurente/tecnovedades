<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones - TecnoVedades</title>
    <link rel="stylesheet" href="<?= url('css/promocion.css') ?>">
</head>

<body>

    <?php
    // Procesar mensajes de estado
    $success = $_GET['success'] ?? '';
    $error = $_GET['error'] ?? '';

    // Obtener estadísticas
    $promocionModel = new \Models\Promocion();
    $stats = $promocionModel->obtenerEstadisticas();
    ?>

    <div class="admin-container">
        <!-- Header del dashboard -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Gestión de Promociones</h1>
            <p class="dashboard-subtitle">Administra cupones, descuentos y ofertas especiales</p>
        </div>

        <!-- Mensajes de estado -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php
                switch ($success) {
                    case 'created':
                        echo 'Promoción creada exitosamente';
                        break;
                    case 'updated':
                        echo 'Promoción actualizada exitosamente';
                        break;
                    case 'deleted':
                        echo 'Promoción eliminada exitosamente';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php
                switch ($error) {
                    case 'not_found':
                        echo 'Promoción no encontrada';
                        break;
                    case 'delete_failed':
                        echo 'Error al eliminar la promoción';
                        break;
                    default:
                        echo 'Ha ocurrido un error';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Panel de estadísticas -->
        <div class="stats-panel">
            <div class="stat-card total">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Promociones</div>
            </div>
            <div class="stat-card activas">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $stats['activas'] ?></div>
                <div class="stat-label">Activas</div>
            </div>
            <div class="stat-card vigentes">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $stats['vigentes'] ?></div>
                <div class="stat-label">Vigentes</div>
            </div>
            <div class="stat-card vencidas">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $stats['vencidas'] ?></div>
                <div class="stat-label">Vencidas</div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="action-buttons">
            <a href="<?= url('promocion/crear') ?>" class="btn-primary">
                Nueva Promoción
            </a>
        </div>

        <!-- Tabla de promociones -->
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
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Vigencia</th>
                                <th>Prioridad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promociones as $promocion01): ?>
                                <?php
                                $condicion = json_decode($promocion01['condicion'], true);
                                $accion = json_decode($promocion01['accion'], true);
                                $ahora = date('Y-m-d');
                                $esVigente = $ahora >= $promocion01['fecha_inicio'] && $ahora <= $promocion01['fecha_fin'];
                                ?>
                                <tr>
                                    <td><strong>#<?= $promocion01['id'] ?></strong></td>
                                    <td class="promotion-name">
                                        <?= htmlspecialchars($promocion01['nombre']) ?>
                                        <?php if ($promocion01['exclusivo']): ?>
                                            <span style="color: #e74c3c; font-size: 0.8rem;">⭐ EXCLUSIVO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tipoClase = '';
                                        $tipoTexto = '';
                                        switch ($accion['tipo'] ?? '') {
                                            case 'descuento_porcentaje':
                                                $tipoClase = 'descuento';
                                                $tipoTexto = $accion['valor'] . '% DESC';
                                                break;
                                            case 'descuento_fijo':
                                                $tipoClase = 'descuento';
                                                $tipoTexto = 'S/' . $accion['valor'] . ' DESC';
                                                break;
                                            case 'envio_gratis':
                                                $tipoClase = 'envio';
                                                $tipoTexto = 'Envío Gratis';
                                                break;
                                            case 'producto_gratis':
                                                $tipoClase = 'producto';
                                                $tipoTexto = 'Producto Gratis';
                                                break;
                                            default:
                                                $tipoTexto = 'Otro';
                                        }
                                        ?>
                                        <span class="promo-type <?= $tipoClase ?>"><?= $tipoTexto ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $promocion01['activo'] ? 'activo' : 'inactivo' ?>">
                                            <?= $promocion01['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                        <?php if ($promocion01['activo']): ?>
                                            <br>
                                            <span class="status-badge status-<?= $esVigente ? 'vigente' : 'vencido' ?>">
                                                <?= $esVigente ? 'Vigente' : 'Vencido' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            <strong>Inicio:</strong> <?= date('d/m/Y', strtotime($promocion01['fecha_inicio'])) ?><br>
                                            <strong>Fin:</strong> <?= date('d/m/Y', strtotime($promocion01['fecha_fin'])) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 10px; font-size: 0.8rem;">
                                            <?= $promocion01['prioridad'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= url('promocion/editar/' . $promocion01['id']) ?>" class="btn-action btn-edit">
                                            Editar
                                        </a>
                                        <a href="<?= url('promocion/toggleEstado/' . $promocion01['id']) ?>" class="btn-action btn-toggle"
                                            onclick="return confirm('¿Cambiar estado de esta promoción?')">
                                            <?= $promocion01['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </a>
                                        <a href="<?= url('promocion/eliminar/' . $promocion01['id']) ?>" class="btn-action btn-delete"
                                            onclick="return confirm('¿Estás seguro de eliminar esta promoción?')">
                                            Eliminar
                                        </a>
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
                        <a href="<?= url('promocion/crear') ?>" class="btn-primary">
                            Crear Primera Promoción
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Botón de regreso -->
        <div style="margin-top: 30px;">
            <a href="<?= url('producto/index') ?>" class="btn-cancel">
                ← Volver al Panel Principal
            </a>
        </div>
    </div>

</body>

</html>