<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/listar.css') ?>">

<body>
    <?php
    $estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    $estadoFiltro = $_GET['estado'] ?? '';

    // Calcular estadísticas
    $estadisticas = [
        'total' => count($pedidos),
        'pendiente' => 0,
        'procesando' => 0,
        'enviado' => 0,
        'entregado' => 0
    ];

    foreach ($pedidos as $pedido) {
        if (isset($estadisticas[$pedido['estado']])) {
            $estadisticas[$pedido['estado']]++;
        }
    }
    ?>
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

                <div class="pedidos-page flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="admin-container">
                        <div class="dashboard-header">
                            <h1 class="dashboard-title">Gestión de Pedidos</h1>
                            <p class="dashboard-subtitle">Administra y supervisa todos los pedidos del sistema</p>
                        </div>

                        <!-- Estadísticas -->
                        <div class="stats-panel">
                            <div class="stat-card total">
                                <div class="stat-icon"></div>
                                <div class="stat-number"><?= $estadisticas['total'] ?></div>
                                <div class="stat-label">Total Pedidos</div>
                            </div>
                            <div class="stat-card pendientes">
                                <div class="stat-icon"></div>
                                <div class="stat-number"><?= $estadisticas['pendiente'] ?></div>
                                <div class="stat-label">Pendientes</div>
                            </div>
                            <div class="stat-card completados">
                                <div class="stat-icon"></div>
                                <div class="stat-number"><?= $estadisticas['procesando'] ?></div>
                                <div class="stat-label">Procesando</div>
                            </div>
                            <div class="stat-card cancelados">
                                <div class="stat-icon"></div>
                                <div class="stat-number"><?= $estadisticas['enviado'] ?></div>
                                <div class="stat-label">Enviados</div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="filters-panel">
                            <h3 class="filters-title">Filtros de Búsqueda</h3>
                            <form method="get" class="filters-grid">
                                <div class="filter-group">
                                    <label class="filter-label">Filtrar por estado:</label>
                                    <select name="estado" class="filter-select">
                                        <option value="">-- Todos los estados --</option>
                                        <?php foreach ($estados as $estado): ?>
                                            <option value="<?= $estado ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>>
                                                <?= ucfirst($estado) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <button type="submit" class="btn-filter">Aplicar filtro</button>
                                </div>
                            </form>
                        </div>

                        <!-- Botones de acción -->
                        <div class="action-buttons">
                            <a href="<?= url('cupon') ?>" class="btn-primary">
                                📋 Gestionar Cupones
                            </a>
                            <a href="<?= url('promocion') ?>" class="btn-secondary">
                                🎯 Gestionar Promociones
                            </a>
                        </div>

                        <!-- Tabla de pedidos -->
                        <?php
                        $pedidosFiltrados = array_filter($pedidos, function ($pedido) use ($estadoFiltro) {
                            return !$estadoFiltro || $pedido['estado'] === $estadoFiltro;
                        });
                        ?>

                        <?php if (!empty($pedidosFiltrados)): ?>
                            <div class="orders-panel">
                                <div class="table-header">
                                    <h3 class="table-title">Lista de Pedidos</h3>
                                    <span class="orders-count"><?= count($pedidosFiltrados) ?> pedidos</span>
                                </div>
                                <div class="table-container">
                                    <table class="orders-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Cliente</th>
                                                <th>Estado</th>
                                                <th>Monto Total</th>
                                                <th>Fecha</th>
                                                <th>Observaciones</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pedidosFiltrados as $pedido): ?>
                                                <tr>
                                                    <td><strong>#<?= $pedido['id'] ?></strong></td>
                                                    <td><?= htmlspecialchars($pedido['cliente_id']) ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= $pedido['estado'] ?>">
                                                            <?= ucfirst($pedido['estado']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="amount">S/ <?= number_format($pedido['monto_total'], 2) ?></td>
                                                    <td class="date"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></td>
                                                    <td>
                                                        <?php if (!empty($pedido['observaciones_admin'])): ?>
                                                            <span title="<?= htmlspecialchars($pedido['observaciones_admin']) ?>">
                                                                <?= substr(htmlspecialchars($pedido['observaciones_admin']), 0, 30) ?>
                                                                <?= strlen($pedido['observaciones_admin']) > 30 ? '...' : '' ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="color: #bdc3c7; font-style: italic;">Sin observaciones</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= url('pedido/ver/' . $pedido['id']) ?>" class="btn-action btn-view">
                                                            Ver detalle
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="orders-panel">
                                <div class="empty-state">
                                    <h3>No se encontraron pedidos</h3>
                                    <p>
                                        <?php if ($estadoFiltro): ?>
                                            No hay pedidos con el estado "<?= ucfirst($estadoFiltro) ?>" en este momento.
                                        <?php else: ?>
                                            Aún no se han registrado pedidos en el sistema.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 30px;">
                            <a href="<?= url('producto/index') ?>" class="back-button">
                                Volver al listado de productos
                            </a>
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