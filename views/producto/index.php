<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <style>
        .producto-card {
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
        }
        .acciones {
            margin-top: 8px;
        }
        .categoria-lista {
            color: #555;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php $base = '/TECNOVEDADES-MASTER/'; ?>

    <h1>Listado de Productos</h1>

    <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $a): ?>
            <div class="producto-card">
                <strong><?= htmlspecialchars($a['nombre']) ?></strong><br>
                <?= htmlspecialchars($a['descripcion']) ?><br>
                Precio: S/ <?= number_format($a['precio'], 2) ?><br>
                Visible: <?= $a['visible'] ? 'Sí' : 'No' ?><br>

                <!-- Mostrar categorías -->
                <?php if (!empty($a['categorias'])): ?>
                    <div class="categoria-lista">
                        Categorías: <?= implode(', ', array_map('htmlspecialchars', $a['categorias'])) ?>
                    </div>
                <?php else: ?>
                    <div class="categoria-lista">Sin categoría</div>
                <?php endif; ?>

                <div class="acciones">
                    <a href="/producto/editar/<?= $producto['id'] ?>">Editar</a> |
                    <a href="/producto/eliminar/<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?>
    <?php foreach ($productos as $producto): ?>
        <li>
            <?= htmlspecialchars($producto['nombre']) ?> - S/ <?= htmlspecialchars($producto['precio']) ?>
            | <a href="<?= $base ?>index.php?url=producto/editar/<?= $producto['id'] ?>">Editar</a>
        <a href="<?= $base ?>index.php?url=producto/eliminar/<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
        </li>
    <?php endforeach; ?>

</body>
</html>
