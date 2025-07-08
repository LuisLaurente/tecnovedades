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
    <h1>Listado de Productos</h1>

    <a href="/producto/crear">+ Nuevo Producto</a><br><br>

    <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                <?= htmlspecialchars($producto['descripcion']) ?><br>
                Precio: S/ <?= number_format($producto['precio'], 2) ?><br>
                Visible: <?= $producto['visible'] ? 'Sí' : 'No' ?><br>

                <!-- Mostrar categorías -->
                <?php if (!empty($producto['categorias'])): ?>
                    <div class="categoria-lista">
                        Categorías: <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
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
</body>
</html>
