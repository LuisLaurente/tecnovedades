<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - TecnoVedades</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .content {
            padding: 30px;
        }
        
        .filters-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-form label {
            color: #2c3e50;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .filter-form label::before {
            content: "🔍";
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .filter-form select {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            min-width: 150px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-form select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .filter-btn::before {
            content: "⚡";
            margin-right: 5px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .orders-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 500;
            position: relative;
        }
        
        .orders-table th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255,255,255,0.3);
        }
        
        .orders-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }
        
        .orders-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .orders-table tr:hover {
            background-color: #e3f2fd;
            transition: background-color 0.2s;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-procesando {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #74c0fc;
        }
        
        .status-enviado {
            background: #d4edda;
            color: #155724;
            border: 1px solid #55a3ff;
        }
        
        .status-entregado {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #00d4aa;
        }
        
        .status-cancelado {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #ff6b6b;
        }
        
        .amount {
            color: #27ae60;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            text-decoration: none;
            color: white;
        }
        
        .action-btn::before {
            content: "👁️";
            margin-right: 5px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid #667eea;
        }
        
        .back-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(-5px);
            text-decoration: none;
        }
        
        .back-link::before {
            content: "🔙";
            margin-right: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .empty-state p {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card.total { border-left-color: #3498db; }
        .stat-card.pendiente { border-left-color: #f39c12; }
        .stat-card.procesando { border-left-color: #2ecc71; }
        .stat-card.enviado { border-left-color: #9b59b6; }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .content {
                padding: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form select,
            .filter-btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .orders-table {
                font-size: 0.85rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 10px 8px;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
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
    
    <div class="container">
        <div class="header">
            <h1>Gestión de Pedidos</h1>
            <p>Administra y supervisa todos los pedidos del sistema</p>
        </div>
        
        <div class="content">
            <!-- Estadísticas -->
            <div class="stats-row">
                <div class="stat-card total">
                    <div class="stat-number"><?= $estadisticas['total'] ?></div>
                    <div class="stat-label">Total Pedidos</div>
                </div>
                <div class="stat-card pendiente">
                    <div class="stat-number"><?= $estadisticas['pendiente'] ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-card procesando">
                    <div class="stat-number"><?= $estadisticas['procesando'] ?></div>
                    <div class="stat-label">Procesando</div>
                </div>
                <div class="stat-card enviado">
                    <div class="stat-number"><?= $estadisticas['enviado'] ?></div>
                    <div class="stat-label">Enviados</div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="filters-section">
                <form method="get" class="filter-form">
                    <label>Filtrar por estado:</label>
                    <select name="estado">
                        <option value="">-- Todos los estados --</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>>
                                <?= ucfirst($estado) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="filter-btn">Aplicar filtro</button>
                </form>
            </div>
            
            <!-- Tabla de pedidos -->
            <?php 
            $pedidosFiltrados = array_filter($pedidos, function($pedido) use ($estadoFiltro) {
                return !$estadoFiltro || $pedido['estado'] === $estadoFiltro;
            });
            ?>
            
            <?php if (!empty($pedidosFiltrados)): ?>
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
                                    <a href="<?= url('pedido/ver/' . $pedido['id']) ?>" class="action-btn">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📦</div>
                    <h3>No se encontraron pedidos</h3>
                    <p>
                        <?php if ($estadoFiltro): ?>
                            No hay pedidos con el estado "<?= ucfirst($estadoFiltro) ?>" en este momento.
                        <?php else: ?>
                            Aún no se han registrado pedidos en el sistema.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <a href="<?= url('producto/index') ?>" class="back-link">
                Volver al listado de productos
            </a>
        </div>
    </div>
</body>
</html>
