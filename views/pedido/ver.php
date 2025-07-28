<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?= $pedido['id'] ?> - TecnoVedades</title>
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
            max-width: 1200px;
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
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header .order-id {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            font-weight: 500;
        }
        
        .content {
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #667eea;
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 1.4rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .section-title .icon {
            margin-right: 10px;
            font-size: 1.3rem;
        }
        
        .order-info {
            display: grid;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #34495e;
        }
        
        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .status-procesando {
            background: #d1ecf1;
            color: #0c5460;
            border: 2px solid #74c0fc;
        }
        
        .status-enviado {
            background: #d4edda;
            color: #155724;
            border: 2px solid #55a3ff;
        }
        
        .status-entregado {
            background: #d1ecf1;
            color: #0c5460;
            border: 2px solid #00d4aa;
        }
        
        .status-cancelado {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #ff6b6b;
        }
        
        .amount {
            color: #27ae60;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .products-table th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 500;
        }
        
        .products-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }
        
        .products-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .products-table tr:hover {
            background-color: #e3f2fd;
            transition: background-color 0.2s;
        }
        
        .form-section {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .form-group label {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .form-group label .icon {
            margin-right: 8px;
        }
        
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }
        
        .btn-success:hover {
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
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
            margin-top: 30px;
        }
        
        .back-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(-5px);
            text-decoration: none;
        }
        
        .back-link .icon {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .content {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 20px;
            }
            
            .form-section {
                grid-template-columns: 1fr;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .products-table {
                font-size: 0.85rem;
            }
            
            .products-table th,
            .products-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <?php
    $estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    ?>
    
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>Detalle del Pedido</h1>
                <div class="order-id">#<?= $pedido['id'] ?></div>
            </div>
        </div>
        
        <div class="content">
            <!-- Información del pedido -->
            <div class="section">
                <h3 class="section-title">
                    <span class="icon">📋</span>
                    Información del Pedido
                </h3>
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">Cliente:</span>
                        <span class="info-value"><?= htmlspecialchars($pedido['cliente_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado:</span>
                        <span class="status-badge status-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Monto total:</span>
                        <span class="info-value amount">S/ <?= number_format($pedido['monto_total'], 2) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de creación:</span>
                        <span class="info-value"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Estado del pedido -->
            <div class="section">
                <h3 class="section-title">
                    <span class="icon">⚡</span>
                    Cambiar Estado
                </h3>
                <form method="post" action="<?= url('pedido/cambiarEstado') ?>">
                    <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                    <div class="form-group">
                        <label for="estado">
                            <span class="icon">🔄</span>
                            Nuevo estado:
                        </label>
                        <select name="estado" id="estado">
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>
                                    <?= ucfirst($estado) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn">
                            <span>🔄</span>
                            Actualizar estado
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Productos del pedido -->
            <div class="section" style="grid-column: 1 / -1;">
                <h3 class="section-title">
                    <span class="icon">📦</span>
                    Productos del Pedido
                </h3>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Variante</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['producto_id']) ?></strong></td>
                                <td><?= htmlspecialchars($item['id']) ?></td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: 500;">
                                        <?= $item['cantidad'] ?>
                                    </span>
                                </td>
                                <td class="amount">S/ <?= number_format($item['precio_unitario'], 2) ?></td>
                                <td class="amount">S/ <?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Observaciones -->
            <div class="form-section">
                <div class="section">
                    <h3 class="section-title">
                        <span class="icon">📝</span>
                        Observaciones del Administrador
                    </h3>
                    <form method="post" action="<?= url('pedido/guardarObservacion') ?>">
                        <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                        <div class="form-group">
                            <label for="observacion">
                                <span class="icon">💬</span>
                                Observaciones:
                            </label>
                            <textarea 
                                name="observacion" 
                                id="observacion" 
                                placeholder="Escribe aquí cualquier observación sobre el pedido..."
                            ><?= htmlspecialchars($pedido['observacion'] ?? '') ?></textarea>
                            <button type="submit" class="btn btn-success">
                                <span>💾</span>
                                Guardar observación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div style="padding: 0 40px 40px;">
            <a href="<?= url('pedido/listar') ?>" class="back-link">
                <span class="icon">🔙</span>
                Volver al listado
            </a>
        </div>
    </div>
</body>
</html>
