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

// ------- Lógica de precios/visibilidad -------
$precioFinal = isset($producto['precio']) ? (float)$producto['precio'] : 0.0;
$precioTachado = isset($producto['precio_tachado']) && $producto['precio_tachado'] !== ''
    ? (float)$producto['precio_tachado'] : null;

$precioTachadoVisible = !empty($producto['precio_tachado_visible']); // 1/0 en BD
$porcentajeVisible    = !empty($producto['porcentaje_visible']);     // 1/0 en BD

// Porcentaje desde BD (si existe)
$descuentoPct = isset($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] !== ''
    ? (float)$producto['porcentaje_descuento'] : 0.0;

// Si el porcentaje no es válido pero sí hay precio tachado > final, lo calculamos
if (($descuentoPct <= 0 || $descuentoPct > 100) && $precioTachado !== null && $precioTachado > 0 && $precioFinal < $precioTachado) {
    $descuentoPct = round((($precioTachado - $precioFinal) / $precioTachado) * 100,2);
}

// Reglas de visibilidad finales
$showTachado = ($precioTachado !== null) && ($precioTachado > $precioFinal) && $precioTachadoVisible;
$showPct     = $porcentajeVisible && $showTachado && ($descuentoPct > 0);

// ------- Ejemplo de ruta de categoría (breadcrumb) -------
// Esto debería venir de tu base de datos o lógica de categorías
// Ejemplo: $breadcrumb = ['Tecnología', 'Celulares', 'iPhone'];
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('/')],
];

// Si el producto tiene categorías, las usamos como rutas intermedias
if (!empty($producto['categorias']) && is_array($producto['categorias'])) {
    foreach ($producto['categorias'] as $categoria) {
        // Puedes personalizar cómo se genera el slug de la categoría aquí
        $slug = strtolower(str_replace(' ', '-', $categoria));
        $breadcrumb[] = ['label' => $categoria, 'url' => url('/')];
    }
}

// Finalmente, agregamos el nombre del producto como breadcrumb actual (sin URL)
$breadcrumb[] = ['label' => $producto['nombre']];

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
        
        <!-- Ruta de categoría (Breadcrumb) -->
        <div class="breadcrumb-container">
            <nav class="breadcrumb" aria-label="Ruta de navegación">
        <?php foreach ($breadcrumb as $index => $item): ?>
            <?php if (isset($item['url']) && $index < count($breadcrumb) - 1): ?>
                <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a>
                <span class="breadcrumb-separator">›</span>
            <?php else: ?>
                <span class="breadcrumb-current"><?= htmlspecialchars($item['label']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

        </div>

        <section class="producto-main">
            <div class="producto-imagenes">
                <?php if (!empty($producto['imagenes'])): ?>
                    <div class="imagen-principal-container">
                        <img
                            id="imagen-principal"
                            src="<?= url('uploads/' . $producto['imagenes'][0]['nombre_imagen']) ?>"
                            alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>
                    <?php if (count($producto['imagenes']) > 1): ?>
                        <div class="miniaturas-container">
                            <?php foreach ($producto['imagenes'] as $index => $imagen): ?>
                                <img
                                    class="miniatura <?= $index === 0 ? 'activo' : '' ?>"
                                    src="<?= url('uploads/' . $imagen['nombre_imagen']) ?>"
                                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                    data-src="<?= url('uploads/' . $imagen['nombre_imagen']) ?>">
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

                <?php if ($showPct): ?>
                    <div class="badge-descuento">-<?= number_format($descuentoPct) ?>%</div>
                <?php endif; ?>

                <p class="producto-descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

                <p class="producto-precio">
                    <span class="precio-final">S/ <?= number_format($precioFinal, 2) ?></span>
                    <?php if ($showTachado): ?>
                        <span class="precio-tachado">S/ <?= number_format($precioTachado, 2) ?></span>
                    <?php endif; ?>
                </p>

                <?php if (!empty($producto['categorias'])): ?>
                    <p class="producto-categorias">
                        <strong>Categorías:</strong>
                        <?php foreach ($producto['categorias'] as $categoria): ?>
                            <span class="categoria-label"><?= htmlspecialchars($categoria) ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

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
                        imagenPrincipal.src = miniatura.dataset.src;
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