<h2>🛒 Carrito de Compras</h2>
<link rel="stylesheet" href="<?= $base ?>css/producto-index.css">
<!-- Volver -->
    <a href="/TECNOVEDADES/public/producto/index" class="boton-volver">🛒 Volver</a>
    <style> 
    .boton-volver {
    background-color:rgb(245, 39, 12);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    float:right;
    position: relative;
    right: 660px;
    }   
    </style>
<?php if (!empty($_SESSION['carrito'])): ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Producto</th>
            <th>Talla</th>
            <th>Color</th>
            <th>Cantidad</th>
            <th>Eliminar</th>
        </tr>
        <?php foreach ($_SESSION['carrito'] as $clave => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['producto_id']) ?></td>
                <td><?= htmlspecialchars($item['talla']) ?></td>
                <td><?= htmlspecialchars($item['color']) ?></td>
                <td><?= htmlspecialchars($item['cantidad']) ?>
                    <a href="/TECNOVEDADES/public/carrito/aumentar/<?= urlencode($clave) ?>">➕</a>
                    <a href="/TECNOVEDADES/public/carrito/disminuir/<?= urlencode($clave) ?>">➖</a>
                </td>
                <td>
                    <a href="/TECNOVEDADES/public/carrito/eliminar/<?= urlencode($clave) ?>" onclick="return confirm('¿Eliminar este producto del carrito?')">❌</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No hay productos en el carrito.</p>
<?php endif; ?>
