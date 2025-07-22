<?php $base = '/TECNOVEDADES/public/'; ?>
<h2>🛒 Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= $base ?>css/producto-index.css">
<a href="<?= $base ?>producto/index" class="boton-volver">🛒 Volver</a>
<style>
    .boton-volver {
        background-color: rgb(245, 39, 12);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        float: right;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: center;
        padding: 10px;
        border: 1px solid #ccc;
    }

    th {
        background-color: #f5f5f5;
    }

    .acciones a {
        margin: 0 5px;
        text-decoration: none;
        font-weight: bold;
    }
</style>

<!-- Contenido del carrito -->
<?php if (!empty($productosDetallados)): ?>
    <table>
        <tr>
            <th>Producto</th>
            <th>Talla</th>
            <th>Color</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Eliminar</th>
        </tr>
        <?php foreach ($productosDetallados as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nombre']) ?></td>
                <td><?= htmlspecialchars($item['talla']) ?></td>
                <td><?= htmlspecialchars($item['color']) ?></td>
                <td>S/ <?= number_format($item['precio'], 2) ?></td>
                <td class="acciones">
                    <?= $item['cantidad'] ?>
                    <a href="<?= $base ?>carrito/aumentar/<?= urlencode($item['clave']) ?>">➕</a>
                    <a href="<?= $base ?>carrito/disminuir/<?= urlencode($item['clave']) ?>">➖</a>
                </td>
                <td>S/ <?= number_format($item['subtotal'], 2) ?></td>
                <td>
                    <a href="<?= $base ?>carrito/eliminar/<?= urlencode($item['clave']) ?>"
                        onclick="return confirm('¿Eliminar este producto del carrito?')">❌</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
            <td colspan="2"><strong>S/ <?= number_format($total, 2) ?></strong></td>
        </tr>
    </table>
<?php else: ?>
    <p>No hay productos en el carrito.</p>
<?php endif; ?>