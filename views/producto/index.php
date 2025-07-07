<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
</head>
<body>
    <?php $base = '/TECNOVEDADES-MASTER/'; ?>

    <h1>Listado de Productos</h1>

    <?php if (!empty($productos)): ?>
        <ul>
            <?php foreach ($productos as $producto): ?>
                <li>
                    <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                    <?= htmlspecialchars($producto['descripcion']) ?><br>
                    Precio: S/ <?= number_format($producto['precio'], 2) ?>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
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
