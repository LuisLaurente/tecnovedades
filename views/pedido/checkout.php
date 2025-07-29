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
$usuario = $_SESSION['usuario'] ?? ['tipo' => 'nuevo']; //se agrega temporalmente ['tipo' => 'nuevo']; para aplicar promo de usuario nuevo
$promociones = PromocionHelper::evaluar($carrito, $usuario);
$totales = PromocionHelper::calcularTotales($carrito, $promociones);

// Aplicar cupón si existe
$cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
$descuento_cupon = 0;
if ($cupon_aplicado) {
    if ($cupon_aplicado['tipo'] === 'descuento_porcentaje') {
        $descuento_cupon = $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
    } elseif ($cupon_aplicado['tipo'] === 'descuento_fijo') {
        $descuento_cupon = min($cupon_aplicado['valor'], $totales['subtotal']); // No puede ser mayor al subtotal
    } elseif ($cupon_aplicado['tipo'] === 'envio_gratis') {
        $totales['envio_gratis'] = true;
        $descuento_cupon = 0; // El envío gratis no afecta el total monetario
    } else {
        // Manejar tipos alternativos que podrían estar en la BD
        if ($cupon_aplicado['tipo'] === 'porcentaje') {
            $descuento_cupon = $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
        } elseif ($cupon_aplicado['tipo'] === 'fijo') {
            $descuento_cupon = min($cupon_aplicado['valor'], $totales['subtotal']);
        }
    }
}

// Recalcular total final considerando descuentos de promociones y cupones por separado
$total_descuentos = $totales['descuento'] + $descuento_cupon;
$totales['total'] = max($totales['subtotal'] - $total_descuentos, 0);
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

                            <!-- Sección de cupones -->
                            <tr>
                                <td colspan="6">
                                    <h4>¿Tienes un cupón?</h4>
                                    <div class="cupon-container">
                                        <input type="text" id="codigo-cupon" placeholder="Ingresa tu cupón" value="<?= $cupon_aplicado['codigo'] ?? '' ?>" <?= isset($cupon_aplicado) ? 'readonly' : '' ?>>
                                        <?php if (isset($cupon_aplicado)): ?>
                                            <button type="button" id="btn-remover-cupon" style="background-color: #e74c3c;">Remover</button>
                                        <?php else: ?>
                                            <button type="button" id="btn-aplicar-cupon">Aplicar</button>
                                        <?php endif; ?>
                                    </div>
                                    <p id="mensaje-cupon" style="color:green;">
                                        <?php if (isset($cupon_aplicado)): ?>
                                            <strong>✓ Cupón aplicado: <?= htmlspecialchars($cupon_aplicado['codigo']) ?></strong>
                                            <?php if ($cupon_aplicado['tipo'] === 'descuento_porcentaje'): ?>
                                                <br><small>Descuento del <?= $cupon_aplicado['valor'] ?>%</small>
                                            <?php elseif ($cupon_aplicado['tipo'] === 'descuento_fijo'): ?>
                                                <br><small>Descuento de S/ <?= number_format($cupon_aplicado['valor'], 2) ?></small>
                                            <?php elseif ($cupon_aplicado['tipo'] === 'envio_gratis'): ?>
                                                <br><small>Envío gratuito</small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>

                            <!-- Totales -->
                            <tr>
                                <td colspan="5" style="text-align: right;">Subtotal:</td>
                                <td>S/ <?= number_format($totales['subtotal'], 2) ?></td>
                            </tr>
                            <?php if ($totales['descuento'] > 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align: right;">Descuento por promociones:</td>
                                    <td>- S/ <?= number_format($totales['descuento'], 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($cupon_aplicado && ($descuento_cupon > 0 || in_array($cupon_aplicado['tipo'], ['descuento_porcentaje', 'descuento_fijo', 'porcentaje', 'fijo']))): ?>
                                <tr style="background-color: #e8f5e8;">
                                    <td colspan="5" style="text-align: right;">
                                        <strong>Descuento cupón (<?= htmlspecialchars($cupon_aplicado['codigo']) ?>):</strong>
                                        <?php if (in_array($cupon_aplicado['tipo'], ['descuento_porcentaje', 'porcentaje'])): ?>
                                            <small>(<?= $cupon_aplicado['valor'] ?>%)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>- S/ <?= number_format($descuento_cupon, 2) ?></strong></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($cupon_aplicado && $cupon_aplicado['tipo'] === 'envio_gratis'): ?>
                                <tr style="background-color: #e8f5e8;">
                                    <td colspan="5" style="text-align: right;">
                                        <strong>Beneficio cupón (<?= htmlspecialchars($cupon_aplicado['codigo']) ?>):</strong>
                                    </td>
                                    <td><strong>🚚 Envío gratis</strong></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td colspan="5" style="text-align: right;"><strong>Total a pagar:</strong></td>
                                <td><strong>S/ <?= number_format($totales['total'], 2) ?></strong></td>
                            </tr>
                            <?php if (!empty($promociones)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="promociones-aplicadas">
                                        <h4>🎉 Promociones aplicada por temporada:</h4>
                                        <ul>
                                            <?php foreach ($promociones as $promo): ?>
                                                <li><?= htmlspecialchars($promo['promocion']['nombre']) ?> (<?= $promo['accion']['tipo'] ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
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
        // Función para aplicar cupón
        function aplicarCupon() {
            const codigo = document.getElementById('codigo-cupon').value;
            if (!codigo.trim()) {
                document.getElementById('mensaje-cupon').innerHTML = '<span style="color: red;">Por favor ingresa un código de cupón</span>';
                return;
            }

            fetch('<?= url("cupon/validar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'codigo=' + encodeURIComponent(codigo)
                })
                .then(r => r.json())
                .then(data => {
                    const mensaje = document.getElementById('mensaje-cupon');

                    if (data.status === 'success') {
                        mensaje.style.color = 'green';
                        mensaje.innerHTML = '<strong>✓ ' + data.mensaje + '</strong>';

                        // Si hay advertencia sobre restricciones, mostrarla
                        if (data.advertencia) {
                            mensaje.innerHTML += '<br><small style="color: orange; font-weight: bold;">⚠️ ' + data.advertencia + '</small>';
                        }

                        // Recargar página para mostrar el descuento aplicado
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        mensaje.style.color = 'red';
                        mensaje.innerHTML = '<strong>✗ ' + data.mensaje + '</strong>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('mensaje-cupon').innerHTML = '<span style="color: red;"><strong>✗ Error al aplicar cupón</strong></span>';
                });
        }

        // Función para remover cupón
        function removerCupon() {
            fetch('<?= url("cupon/remover") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error al remover cupón');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al remover cupón');
                });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const btnAplicar = document.getElementById('btn-aplicar-cupon');
            const btnRemover = document.getElementById('btn-remover-cupon');

            if (btnAplicar) {
                btnAplicar.addEventListener('click', aplicarCupon);
            }

            if (btnRemover) {
                btnRemover.addEventListener('click', function() {
                    if (confirm('¿Estás seguro de que quieres remover el cupón?')) {
                        removerCupon();
                    }
                });
            }

            // Permitir aplicar cupón con Enter
            document.getElementById('codigo-cupon').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && btnAplicar) {
                    aplicarCupon();
                }
            });
        });
    </script>
</body>

</html>