<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$errores = [];
if (isset($_SESSION['errores_checkout']) && is_array($_SESSION['errores_checkout'])) {
    $errores = $_SESSION['errores_checkout'];
}
unset($_SESSION['errores_checkout']);
$base = '/TECNOVEDADES/public/';

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
<h3>Resumen de tu compra</h3>
<?php if (!empty($productosDetallados)): ?>
    <style>
        .checkout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .checkout-table th, .checkout-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .checkout-table th {
            background-color: #f2f2f2;
        }
        .checkout-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .checkout-table tr:last-child td {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>
    <table class="checkout-table">
        <tr>
            <th>Producto</th>
            <th>Talla</th>
            <th>Color</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($productosDetallados as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nombre']) ?></td>
                <td><?= htmlspecialchars($item['talla']) ?></td>
                <td><?= htmlspecialchars($item['color']) ?></td>
                <td>S/ <?= number_format($item['precio'], 2) ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td>S/ <?= number_format($item['subtotal'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
            <td><strong>S/ <?= number_format($total, 2) ?></strong></td>
        </tr>
    </table>
<?php else: ?>
    <p>No hay productos en el carrito.</p>
<?php endif; ?>

<h2>Finalizar compra</h2>
<?php if ($errores): ?>
    <ul style="color:red;">
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<form method="post" action="/TECNOVEDADES/public/pedido/registrar">
    <label>Nombre completo:<br>
        <input type="text" name="nombre" required>
    </label><br><br>
    <label>Dirección:<br>
        <input type="text" name="direccion" required>
    </label><br><br>
    <label>Teléfono:<br>
        <input type="text" name="telefono">
    </label><br><br>
    <label>Correo:<br>
        <input type="email" name="correo">
    </label><br><br>
    <button type="submit">Confirmar pedido</button>
</form>
<a href="<?= $base ?>carrito/ver">&larr; Volver al carrito</a>
