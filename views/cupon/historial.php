<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Uso - Cupón <?= htmlspecialchars($cupon['codigo']) ?></title>
    <link rel="stylesheet" href="<?= url('css/cupon.css') ?>">
    <style>
        .historial-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .cupon-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .historial-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .historial-table th,
        .historial-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .historial-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.vigente { background: #d4edda; color: #155724; }
        .badge.expirado { background: #f8d7da; color: #721c24; }
        .badge.inactivo { background: #e2e3e5; color: #383d41; }
        .badge.pendiente { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
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
</body>
</html>
