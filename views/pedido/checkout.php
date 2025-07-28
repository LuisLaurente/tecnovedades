<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$errores = [];
if (isset($_SESSION['errores_checkout']) && is_array($_SESSION['errores_checkout'])) {
    $errores = $_SESSION['errores_checkout'];
}
unset($_SESSION['errores_checkout']);

// Resumen de compra igual que en carrito/ver.php
$productosDetallados = [];
$total = 0;
if (!empty($_SESSION['carrito'])) {
    $productoModel = new \Models\Producto();
    foreach ($_SESSION['carrito'] as $clave => $item) {
        $producto = $productoModel->obtenerPorId($item['producto_id']);
        if ($producto) {
            $producto['nombre'] = $producto['nombre'];
            $producto['cantidad'] = $item['cantidad'];
            $producto['talla'] = $item['talla'];
            $producto['color'] = $item['color'];
            $producto['clave'] = $clave;
            $producto['precio'] = $item['precio'];
            $producto['subtotal'] = $producto['precio'] * $item['cantidad'];
            $total += $producto['subtotal'];
            $productosDetallados[] = $producto;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TecnoVedades</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
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
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .summary-section, .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "🛒";
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .form-section .section-title::before {
            content: "📝";
        }
        
        .error-alerts {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .error-alerts ul {
            list-style: none;
        }
        
        .error-alerts li {
            padding: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .error-alerts li::before {
            content: "⚠️";
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Finalizar Compra</h1>
            <p>Revisa tu pedido y completa tus datos</p>
        </div>
        
        <div class="content">
            <div class="summary-section">
                <h3 class="section-title">Resumen de tu compra</h3>
                <?php if (!empty($productosDetallados)): ?>
                    <style>
                        .checkout-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        }
                        .checkout-table th {
                            background: linear-gradient(135deg, #3498db, #2980b9);
                            color: white;
                            padding: 15px 10px;
                            text-align: center;
                            font-weight: 500;
                            font-size: 0.9rem;
                        }
                        .checkout-table td {
                            padding: 12px 10px;
                            text-align: center;
                            border-bottom: 1px solid #eee;
                            font-size: 0.9rem;
                        }
                        .checkout-table tr:nth-child(even) {
                            background-color: #f8f9fa;
                        }
                        .checkout-table tr:hover {
                            background-color: #e3f2fd;
                            transition: background-color 0.2s;
                        }
                        .checkout-table .total-row {
                            background: linear-gradient(135deg, #27ae60, #2ecc71) !important;
                            color: white;
                            font-weight: bold;
                            font-size: 1rem;
                        }
                        .checkout-table .total-row td {
                            border-bottom: none;
                        }
                        .product-name {
                            font-weight: 500;
                            color: #2c3e50;
                        }
                        .price {
                            color: #27ae60;
                            font-weight: 500;
                        }
                    </style>
                    <table class="checkout-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosDetallados as $item): ?>
                                <tr>
                                    <td class="product-name"><?= htmlspecialchars($item['nombre']) ?></td>
                                    <td><?= htmlspecialchars($item['talla']) ?></td>
                                    <td><?= htmlspecialchars($item['color']) ?></td>
                                    <td class="price">S/ <?= number_format($item['precio'], 2) ?></td>
                                    <td><span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem;"><?= $item['cantidad'] ?></span></td>
                                    <td class="price">S/ <?= number_format($item['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="5" style="text-align: right; padding-right: 20px;">Total a pagar:</td>
                                <td>S/ <?= number_format($total, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                        <div style="font-size: 3rem; margin-bottom: 15px;">🛒</div>
                        <p style="font-size: 1.1rem;">No hay productos en el carrito.</p>
                        <a href="<?= url('producto/index') ?>" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">Ver productos</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <h3 class="section-title">Datos de entrega</h3>
                
                <?php if ($errores): ?>
                    <div class="error-alerts">
                        <ul>
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <style>
                    .checkout-form {
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                    }
                    
                    .form-group {
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .form-group label {
                        color: #2c3e50;
                        font-weight: 500;
                        margin-bottom: 8px;
                        display: flex;
                        align-items: center;
                    }
                    
                    .form-group label::before {
                        margin-right: 8px;
                        font-size: 1.1rem;
                    }
                    
                    .form-group:nth-child(1) label::before { content: "👤"; }
                    .form-group:nth-child(2) label::before { content: "📍"; }
                    .form-group:nth-child(3) label::before { content: "📱"; }
                    .form-group:nth-child(4) label::before { content: "✉️"; }
                    
                    .form-group input {
                        padding: 12px 15px;
                        border: 2px solid #e9ecef;
                        border-radius: 8px;
                        font-size: 1rem;
                        transition: all 0.3s ease;
                        background: white;
                    }
                    
                    .form-group input:focus {
                        outline: none;
                        border-color: #3498db;
                        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                        transform: translateY(-2px);
                    }
                    
                    .form-group input:required {
                        border-left: 4px solid #e74c3c;
                    }
                    
                    .form-group input:required:valid {
                        border-left: 4px solid #27ae60;
                    }
                    
                    .submit-btn {
                        background: linear-gradient(135deg, #27ae60, #2ecc71);
                        color: white;
                        padding: 15px 30px;
                        border: none;
                        border-radius: 8px;
                        font-size: 1.1rem;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        margin-top: 10px;
                        position: relative;
                        overflow: hidden;
                    }
                    
                    .submit-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
                    }
                    
                    .submit-btn:active {
                        transform: translateY(0);
                    }
                    
                    .submit-btn::before {
                        content: "🛍️";
                        margin-right: 8px;
                    }
                    
                    .back-link {
                        display: inline-flex;
                        align-items: center;
                        color: #3498db;
                        text-decoration: none;
                        font-weight: 500;
                        margin-top: 20px;
                        padding: 10px 15px;
                        border-radius: 5px;
                        transition: all 0.3s ease;
                    }
                    
                    .back-link:hover {
                        background: #e3f2fd;
                        transform: translateX(-5px);
                    }
                    
                    .back-link::before {
                        content: "🔙";
                        margin-right: 8px;
                    }
                </style>

                <form method="post" action="<?= url('pedido/registrar') ?>" class="checkout-form">
                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ingresa tu nombre completo">
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección de entrega *</label>
                        <input type="text" id="direccion" name="direccion" required placeholder="Calle, número, distrito, ciudad">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="999 999 999">
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="tu@email.com">
                    </div>
                    
                    <button type="submit" class="submit-btn">Confirmar pedido</button>
                </form>
                
                <a href="<?= url('carrito/ver') ?>" class="back-link">Volver al carrito</a>
            </div>
        </div>
    </div>
</body>
</html>
