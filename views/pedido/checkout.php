<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Core\Helpers\PromocionHelper;
use Models\Cupon;

$errores = [];
if (isset($_SESSION['errores_checkout']) && is_array($_SESSION['errores_checkout'])) {
    $errores = $_SESSION['errores_checkout'];
}
unset($_SESSION['errores_checkout']);

// Preparar datos de carrito
$productosDetallados = [];
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

if (!empty($carrito)) {
    $productoModel = new \Models\Producto();
    foreach ($carrito as $clave => $item) {
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

// Calcular promociones y totales
$usuario = $_SESSION['usuario'] ?? null;
$promociones = PromocionHelper::evaluar($carrito, $usuario);
$totales = PromocionHelper::calcularTotales($carrito, $promociones);

// Aplicar cupón si existe
$cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
if ($cupon_aplicado) {
    if ($cupon_aplicado['tipo'] === 'descuento_porcentaje') {
        $totales['descuento'] += $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
    } elseif ($cupon_aplicado['tipo'] === 'descuento_fijo') {
        $totales['descuento'] += $cupon_aplicado['valor'];
    } elseif ($cupon_aplicado['tipo'] === 'envio_gratis') {
        $totales['envio_gratis'] = true;
    }
    $totales['total'] = max($totales['subtotal'] - $totales['descuento'], 0);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= url('css/checkout.css') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TecnoVedades</title>
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

                            <!-- Sección de cupon -->
                            <tr>
                                <td colspan="6">
                                    <h4>¿Tienes un cupón?</h4>
                                    <div class="cupon-container">
                                        <input type="text" id="codigo-cupon" placeholder="Ingresa tu cupón" value="<?= $cupon_aplicado['codigo'] ?? '' ?>">
                                        <button type="button" id="btn-aplicar-cupon">Aplicar</button>
                                    </div>
                                    <p id="mensaje-cupon" style="color:green;">
                                        <?= isset($cupon_aplicado) ? 'Cupón aplicado: ' . htmlspecialchars($cupon_aplicado['codigo']) : '' ?>
                                    </p>
                                </td>
                            </tr>

                            <!-- Totales -->
                            <tr>
                                <td colspan="5" style="text-align: right;">Subtotal:</td>
                                <td>S/ <?= number_format($totales['subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="text-align: right;">Descuento:</td>
                                <td>- S/ <?= number_format($totales['descuento'], 2) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="5" style="text-align: right;"><strong>Total a pagar:</strong></td>
                                <td><strong>S/ <?= number_format($totales['total'], 2) ?></strong></td>
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

            <!-- Formulario de entrega -->
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

    <!-- Script para aplicar cupon -->
    <script>
    document.getElementById('btn-aplicar-cupon').addEventListener('click', function(){
        const codigo = document.getElementById('codigo-cupon').value;
        fetch('<?= url("cupon/validar") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'codigo=' + encodeURIComponent(codigo)
        })
        .then(r => r.json())
        .then(data => {
            const mensaje = document.getElementById('mensaje-cupon');
            mensaje.style.color = data.status === 'success' ? 'green' : 'red';
            mensaje.innerText = data.mensaje;
            if (data.status === 'success') {
                location.reload();
            }
        });
    });
    </script>
</body>
</html>
