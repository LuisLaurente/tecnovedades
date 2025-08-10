<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cantidadEnCarrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += $item['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($producto['nombre']) ?> - Bytebox</title>
    <link rel="stylesheet" href="<?= url('css/producto-descripcion.css') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <main class="producto-descripcion-container">

        <section class="producto-main">
    <div class="producto-imagenes">
        <?php if (!empty($producto['imagenes'])): ?>
            <div class="imagen-principal-container">
                <img
                    id="imagen-principal"
                    src="<?= url('uploads/' . $producto['imagenes'][0]['nombre_imagen']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                >
            </div>
            <?php if (count($producto['imagenes']) > 1): ?>
                <div class="miniaturas-container">
                    <?php foreach ($producto['imagenes'] as $index => $imagen): ?>
                        <img
                            class="miniatura <?= $index === 0 ? 'activo' : '' ?>"
                            src="<?= url('uploads/' . $imagen['nombre_imagen']) ?>"
                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            data-src="<?= url('uploads/' . $imagen['nombre_imagen']) ?>"
                        >
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="imagen-principal-container">
                <img src="<?= url('uploads/default-product.png') ?>" alt="Sin imagen">
            </div>
        <?php endif; ?>
    </div>

    <div class="producto-info">
        <h1 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h1>
        <p class="producto-descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

        <p class="producto-precio">
            <strong>Precio:</strong> S/ <?= number_format($producto['precio'], 2) ?>
        </p>

        <p class="producto-categorias">
            <strong>Categorías:</strong>
            <?php foreach ($producto['categorias'] as $categoria): ?>
                <span class="categoria-label"><?= htmlspecialchars($categoria) ?></span>
            <?php endforeach; ?>
        </p>

        <form method="POST" action="<?= url('carrito/agregar') ?>" class="add-to-cart-form">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" name="cantidad" value="1" min="1" class="cantidad-input">
            <button type="submit" class="btn-agregar">Agregar al carrito</button>
        </form>
    </div>
</section>

<script>
    // Cambiar imagen principal al hacer click en miniatura
    document.addEventListener('DOMContentLoaded', function () {
        const imagenPrincipal = document.getElementById('imagen-principal');
        const miniaturas = document.querySelectorAll('.miniaturas-container .miniatura');

        miniaturas.forEach(miniatura => {
            miniatura.addEventListener('click', () => {
                // Cambiar src imagen principal
                imagenPrincipal.src = miniatura.dataset.src;

                // Actualizar clase activo
                miniaturas.forEach(m => m.classList.remove('activo'));
                miniatura.classList.add('activo');
            });
        });
    });
</script>


    </main>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

</body>

</html>