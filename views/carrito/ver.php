<h2>🛒 Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= url('css/producto-index.css') ?>">
<a href="<?= url('producto/index') ?>" class="boton-volver">🛒 Volverrrr</a>
<a href="<?= url('pedido/checkout') ?>" class="boton-checkout">Finalizar compra</a>


<style>
    .boton-checkout {
        background-color: #28a745;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        float: left;
        margin-bottom: 20px;
        margin-right: 10px;
    }
    /* Variables específicas del carrito */
    :root {
        --color-danger-light: #f8d7da;
        --color-success-light: #d4edda;
        --color-info-light: #d1ecf1;
    }

    /* Header del carrito */
    h2 {
        color: var(--color-primary);
        font-size: 2.2rem;
        font-weight: 600;
        margin-bottom: 25px;
        text-align: center;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .boton-volver {
        background: linear-gradient(135deg, var(--color-danger), var(--color-danger-dark));
        color: white;
        padding: 12px 20px;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: bold;
        float: right;
        margin-bottom: 30px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: var(--shadow);
    }

    .boton-volver:hover {
        background: linear-gradient(135deg, var(--color-danger-dark), #871e2b);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        color: white;
        text-decoration: none;
    }

    /* Contenedor de la tabla */
    .tabla-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    th {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
        color: white;
        padding: 18px 15px;
        text-align: center;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    td {
        padding: 18px 15px;
        text-align: center;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
        background: white;
        transition: background-color 0.2s ease;
    }

    tr:hover td {
        background: #f8f9fa;
    }

    tr:last-child td {
        border-bottom: none;
    }

    /* Celdas específicas */
    .producto-nombre {
        font-weight: 600;
        color: var(--color-text);
        text-align: left;
        max-width: 250px;
        word-wrap: break-word;
    }

    .producto-precio,
    .producto-subtotal {
        font-weight: 700;
        color: var(--color-success);
        font-size: 1.05rem;
    }

    .producto-talla,
    .producto-color {
        font-weight: 500;
        color: var(--color-text-light);
        font-style: italic;
    }

    /* Cantidad y acciones */
    .cantidad-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cantidad-numero {
        font-weight: bold;
        font-size: 1.1rem;
        color: var(--color-primary);
        min-width: 30px;
    }

    .acciones a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin: 0 3px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 50%;
        transition: var(--transition);
        font-size: 1rem;
    }

    .btn-aumentar {
        background: linear-gradient(135deg, var(--color-success), var(--color-success-dark));
        color: white;
    }

    .btn-disminuir {
        background: linear-gradient(135deg, var(--color-warning), #e68900);
        color: white;
    }

    .btn-eliminar {
        background: linear-gradient(135deg, var(--color-danger), var(--color-danger-dark));
        color: white;
    }

    .btn-aumentar:hover,
    .btn-disminuir:hover,
    .btn-eliminar:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Fila de total */
    .total-row {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
        border-top: 3px solid var(--color-primary);
    }

    .total-row td {
        font-size: 1.2rem;
        font-weight: bold;
        padding: 25px 15px;
        background: transparent !important;
    }

    .total-label {
        color: var(--color-text);
        text-align: right;
    }

    .total-amount {
        color: var(--color-success);
        font-size: 1.4rem;
    }

    /* Mensaje carrito vacío */
    .carrito-vacio {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        color: var(--color-text-light);
        font-size: 1.2rem;
        border: 2px dashed #ddd;
    }

    .carrito-vacio-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        h2 {
            font-size: 1.8rem;
            flex-direction: column;
            gap: 15px;
        }

        .boton-volver {
            float: none;
            display: block;
            text-align: center;
            margin: 0 auto 20px auto;
        }

        table {
            font-size: 0.85rem;
        }

        th,
        td {
            padding: 12px 8px;
        }

        .producto-nombre {
            max-width: 150px;
            font-size: 0.9rem;
        }

        .cantidad-container {
            flex-direction: column;
            gap: 8px;
        }

        .acciones a {
            width: 28px;
            height: 28px;
            font-size: 0.9rem;
        }

        .total-row td {
            font-size: 1.1rem;
            padding: 20px 8px;
        }

        .total-amount {
            font-size: 1.2rem;
        }
    }

    @media (max-width: 480px) {

        /* Tabla horizontal scroll en móviles muy pequeños */
        .tabla-container {
            overflow-x: auto;
        }

        table {
            min-width: 600px;
        }

        th,
        td {
            padding: 10px 6px;
            font-size: 0.8rem;
        }

        .carrito-vacio {
            padding: 40px 15px;
            font-size: 1rem;
        }

        .carrito-vacio-icon {
            font-size: 3rem;
        }
    }

    /* Animaciones */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tabla-container {
        animation: slideIn 0.3s ease-out;
    }

    /* Clearfix para el float */
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
</style>

<!-- Contenido del carrito -->
<div class="clearfix"></div>
<?php if (!empty($productosDetallados)): ?>
    <div class="tabla-container">
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
                    <td class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></td>
                    <td class="producto-talla"><?= htmlspecialchars($item['talla']) ?></td>
                    <td class="producto-color"><?= htmlspecialchars($item['color']) ?></td>
                    <td class="producto-precio">S/ <?= number_format($item['precio'], 2) ?></td>
                    <td>
                        <div class="cantidad-container">
                            <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-disminuir" title="Disminuir cantidad">➖</a>
                            <span class="cantidad-numero"><?= $item['cantidad'] ?></span>
                            <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-aumentar" title="Aumentar cantidad">➕</a>
                        </div>
                    </td>
                    <td class="producto-subtotal">S/ <?= number_format($item['subtotal'], 2) ?></td>
                    <td>
                        <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>"
                         class="btn-eliminar"
                         title="Eliminar producto"
                         onclick="return confirm('¿Eliminar este producto del carrito?')">🗑️</a>>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" class="total-label">💰 Total:</td>
                <td colspan="2" class="total-amount">S/ <?= number_format($total, 2) ?></td>
            </tr>
        </table>
    </div>
<?php else: ?>
    <div class="carrito-vacio">
        <div class="carrito-vacio-icon">🛒</div>
        <p>Tu carrito está vacío</p>
        <p style="font-size: 0.9rem; margin-top: 10px;">¡Agrega algunos productos para comenzar!</p>
    </div>
<?php endif; ?>