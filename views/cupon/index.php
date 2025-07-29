<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Cupones - TecnoVedades</title>
    <link rel="stylesheet" href="<?= url('css/cupon.css') ?>">
</head>
<body>
    <div class="cupon-admin">
        <!-- Header -->
        <div class="cupon-header">
            <h1>Administración de Cupones</h1>
            <a href="<?= url('cupon/crear') ?>" class="btn-nuevo-cupon">
                <span>+</span> Nuevo Cupón
            </a>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch ($_GET['success']) {
                    case 'created':
                        echo 'Cupón creado exitosamente';
                        break;
                    case 'updated':
                        echo 'Cupón actualizado exitosamente';
                        break;
                    case 'status_changed':
                        echo 'Estado del cupón cambiado exitosamente';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                switch ($_GET['error']) {
                    case 'not_found':
                        echo 'Cupón no encontrado';
                        break;
                    case 'status_change_failed':
                        echo 'Error al cambiar el estado del cupón';
                        break;
                    default:
                        echo 'Ha ocurrido un error';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="estadisticas-cupones">
            <div class="stat-card total">
                <h3>Total de Cupones</h3>
                <p class="numero"><?= $estadisticas['total'] ?? 0 ?></p>
            </div>
            <div class="stat-card activos">
                <h3>Cupones Activos</h3>
                <p class="numero"><?= $estadisticas['activos'] ?? 0 ?></p>
            </div>
            <div class="stat-card vigentes">
                <h3>Cupones Vigentes</h3>
                <p class="numero"><?= $estadisticas['vigentes'] ?? 0 ?></p>
            </div>
            <div class="stat-card usados">
                <h3>Cupones Usados</h3>
                <p class="numero"><?= $estadisticas['usados'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Tabla de cupones -->
        <div class="cupones-tabla-container">
            <?php if (empty($cupones)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    <h3>No hay cupones registrados</h3>
                    <p>Comienza creando tu primer cupón</p>
                    <a href="<?= url('cupon/crear') ?>" class="btn-primary">Crear Cupón</a>
                </div>
            <?php else: ?>
                <table class="cupones-tabla">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Usos</th>
                            <th>Límites</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cupones as $cupon001): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cupon001['codigo']) ?></strong>
                                </td>
                                <td>
                                    <span class="tipo-cupon <?= $cupon001['tipo'] === 'porcentaje' ? 'tipo-porcentaje' : 'tipo-monto' ?>">
                                        <?= $cupon001['tipo'] === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cupon001['tipo'] === 'porcentaje'): ?>
                                        <?= $cupon001['valor'] ?>%
                                    <?php else: ?>
                                        S/ <?= number_format($cupon001['valor'], 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        <div><?= date('d/m/Y', strtotime($cupon001['fecha_inicio'])) ?></div>
                                        <div style="color: #666;">al <?= date('d/m/Y', strtotime($cupon001['fecha_fin'])) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="estado-badge estado-<?= $cupon001['estado_vigencia'] ?>">
                                        <?php
                                        switch ($cupon001['estado_vigencia']) {
                                            case 'vigente':
                                                echo 'Vigente';
                                                break;
                                            case 'expirado':
                                                echo 'Expirado';
                                                break;
                                            case 'pendiente':
                                                echo 'Pendiente';
                                                break;
                                            case 'inactivo':
                                                echo 'Inactivo';
                                                break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="text-align: center;">
                                        <strong><?= $cupon001['usos_totales'] ?></strong>
                                        <?php if ($cupon001['limite_uso']): ?>
                                            <div style="font-size: 0.8rem; color: #666;">
                                                / <?= $cupon001['limite_uso'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        <?php if ($cupon001['monto_minimo'] > 0): ?>
                                            <div>Min: S/ <?= number_format($cupon001['monto_minimo'], 2) ?></div>
                                        <?php endif; ?>
                                        <?php if ($cupon001['limite_por_usuario']): ?>
                                            <div>Por usuario: <?= $cupon001['limite_por_usuario'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="acciones">
                                        <a href="<?= url('cupon/historial?id=' . $cupon001['id']) ?>" class="btn-accion btn-ver" 
                                           title="Ver historial de uso">
                                            Historial
                                        </a>
                                        <a href="<?= url('cupon/editar/' . $cupon001['id']) ?>" class="btn-accion btn-editar">
                                            Editar
                                        </a>
                                        <form method="POST" action="<?= url('cupon/toggleEstado/' . $cupon001['id']) ?>" style="display: inline;">
                                            <button type="submit" class="btn-accion btn-toggle">
                                                <?= $cupon001['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Confirmación para eliminar
        function confirmarEliminacion(codigo) {
            return confirm(`¿Estás seguro de eliminar el cupón "${codigo}"?`);
        }

        // Auto-ocultar alertas
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
