<?php
// views/producto/detalle.php
// Requiere: $producto (array) -- pasarlo desde el controlador
// Opcionales: $breadcrumb (array), $relatedProducts (array), $reviews (array)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cantidad total en el carrito (para mostrar si lo necesitas en header)
$cantidadEnCarrito = 0;
if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += (int)($item['cantidad'] ?? 0);
    }
}

// Fallbacks mínimos para $producto si no están definidos (para evitar errores en desarrollo)
$producto = $producto ?? [
    'id' => 0,
    'nombre' => 'Producto de ejemplo',
    'descripcion' => 'Descripción breve del producto. Reemplaza con datos reales.',
    'precio' => 0,
    'precio_tachado' => null,
    'porcentaje_descuento' => 0,
    'precio_tachado_visible' => 0,
    'porcentaje_visible' => 0,
    'imagenes' => [],
    'categorias' => [],
    'stock' => null,
];

// Preparar precios y flags (reutiliza tu lógica)
$precioFinal = isset($producto['precio']) ? (float)$producto['precio'] : 0.0;
$precioTachado = isset($producto['precio_tachado']) && $producto['precio_tachado'] !== ''
    ? (float)$producto['precio_tachado'] : null;

$precioTachadoVisible = !empty($producto['precio_tachado_visible']);
$porcentajeVisible    = !empty($producto['porcentaje_visible']);

$descuentoPct = isset($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] !== ''
    ? (float)$producto['porcentaje_descuento'] : 0.0;

if (($descuentoPct <= 0 || $descuentoPct > 100) && $precioTachado !== null && $precioTachado > 0 && $precioFinal < $precioTachado) {
    $descuentoPct = round((($precioTachado - $precioFinal) / $precioTachado) * 100, 2);
}

$showTachado = ($precioTachado !== null) && ($precioTachado > $precioFinal) && $precioTachadoVisible;
$showPct     = $porcentajeVisible && $showTachado && ($descuentoPct > 0);

// Breadcrumb fallback
$breadcrumb = $breadcrumb ?? ($producto['breadcrumb'] ?? ['Inicio', 'Productos']);

// Related products & reviews fallback (estructura vacía lista para poblar desde DB)
$relatedProducts = $relatedProducts ?? []; // Cada item: ['id'=>..., 'nombre'=>..., 'imagenes'=>[['nombre_imagen'=>...]], 'precio'=>...]
$reviews = $reviews ?? []; // Cada item: ['autor'=>..., 'puntuacion'=>5, 'fecha'=>..., 'texto'=>...]

// helper para obtener imagen (primera o default)
function producto_imagen_url($producto, $idx = 0)
{
    if (!empty($producto['imagenes']) && isset($producto['imagenes'][$idx]['nombre_imagen'])) {
        return url('uploads/' . $producto['imagenes'][$idx]['nombre_imagen']);
    }
    return url('uploads/default-product.png');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($producto['nombre']) ?> - Bytebox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS del diseño -->
    <link rel="stylesheet" href="<?= url('css/producto-descripcion.css') ?>">
    <!-- Si tienes cards.css u otros, puedes incluirlos aquí -->
    <link rel="stylesheet" href="<?= url('css/cards.css') ?>">
</head>

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    <main class="product-detail-container">
        <section class="product-info2">
            <div class="product-image-gallery">
                <div class="bread-links">
                    <nav class="breadcrumbs" aria-label="Breadcrumb">
                        <ol class="breadcrumb-list">
                            <!-- Enlace fijo al inicio -->
                            <li class="breadcrumb-item">
                                <a href="<?= url('/') ?>">Inicio</a>
                            </li>
                            <?php if (is_array($breadcrumb) && !empty($breadcrumb)): ?>
                                <?php foreach ($breadcrumb as $i => $c): ?>
                                    <?php $isLast = ($i === count($breadcrumb) - 1); ?>
                                    <li class="breadcrumb-item <?= $isLast ? 'crumb-current' : '' ?>" <?= $isLast ? 'aria-current="page"' : '' ?>>
                                        <?php if ($isLast): ?>
                                            <?= htmlspecialchars($c) ?>
                                        <?php else: ?>
                                            <a href="<?= url('categoria/' . rawurlencode(strtolower(str_replace(' ', '-', $c)))) ?>">
                                                <?= htmlspecialchars($c) ?>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback si no hay breadcrumb dinámico -->
                                <li class="breadcrumb-item crumb-current" aria-current="page">Productos</li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
                <img id="main-product-image"
                    src="<?= producto_imagen_url($producto, 0) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                    class="main-product-image">

                <?php if (!empty($producto['imagenes']) && count($producto['imagenes']) > 1): ?>
                    <div class="thumbnail-images" role="list">
                        <?php foreach ($producto['imagenes'] as $idx => $img):
                            $imgUrl = url('uploads/' . $img['nombre_imagen']);
                        ?>
                            <img class="thumb <?= $idx === 0 ? 'activo' : '' ?>"
                                src="<?= $imgUrl ?>"
                                data-src="<?= $imgUrl ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?> miniatura <?= $idx + 1 ?>"
                                role="listitem">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-details">
                <h1 class="fade-text"><?= htmlspecialchars($producto['nombre']) ?></h1>

                <div class="rating fade-text">
                    <a href="#reviews-section" class="rating-link">
                        <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <span class="rating-count">(<?= isset($producto['rating_count']) ? (int)$producto['rating_count'] : '0' ?>)</span>
                    </a>
                </div>


                <div class="price fade-text">
                    <span class="current-price">S/ <?= number_format($precioFinal, 2) ?></span>
                    <?php if ($showTachado): ?>
                        <span class="old-price">S/ <?= number_format($precioTachado, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($showPct): ?>
                        <span class="discount">-<?= number_format($descuentoPct) ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="quantity-selector fade-text" aria-label="Seleccionar cantidad">
                    <button type="button" id="qty-decrease" aria-label="Disminuir cantidad">−</button>
                    <input type="number" id="qty-input" name="cantidad" value="1" min="1" step="1" class="qty-input" />
                    <button type="button" id="qty-increase" aria-label="Aumentar cantidad">+</button>
                    <?php if (isset($producto['stock']) && $producto['stock'] !== null): ?>
                        <span class="stock-info">Stock: <?= (int)$producto['stock'] ?> unidades</span>
                    <?php endif; ?>
                </div>

                <form method="POST" action="<?= url('carrito/agregar') ?>" class="add-to-cart-form">
                    <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
                    <input type="hidden" name="cantidad" id="form-cantidad" value="1">
                    <button type="submit" class="add-to-cart-btn">Agregar al Carro</button>
                </form>

                <div class="delivery-options fade-text">
                    <div class="option">
                        <img src="<?= url('images/delivery_icon.png') ?>" alt="Despacho a domicilio">
                        <span>Despacho a domicilio</span>
                    </div>
                    <div class="option">
                        <img src="<?= url('images/pickup_icon.png') ?>" alt="Retira tu compra">
                        <span>Retira tu compra</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Descripción / Especificaciones (colapsables) -->
        <section class="collapsible-section">
            <h2 class="collapsible-header">Descripción <span class="arrow">&#9660;</span></h2>
            <div class="collapsible-content">
                <?php if (!empty($producto['descripcion_larga'])): ?>
                    <?= nl2br(htmlspecialchars($producto['descripcion_larga'])) ?>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($producto['descripcion'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="collapsible-section">
            <h2 class="collapsible-header">Especificaciones <span class="arrow">&#9660;</span></h2>
            <div class="collapsible-content">
                <?php if (!empty($producto['especificaciones_array'])): ?>
                    <ul>
                        <?php foreach ($producto['especificaciones_array'] as $spec): ?>
                            <li><?= htmlspecialchars($spec) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay especificaciones detalladas.</p>
                <?php endif; ?>
            </div>
        </section>


        <!-- Related products (plantilla lista para poblar desde BD) -->
        <section class="related-products">
            <h2>Productos Relacionados</h2>

            <?php if (!empty($relatedProducts) && is_array($relatedProducts)): ?>
                <div class="products-carousel-container" aria-label="Carrusel de productos relacionados">
                    <?php
                    // --- PREPARAR la variable que la parcial espera ---
                    // Guardamos cualquier $productos que pudiera existir para no romper otras inclusiones
                    $__productos_backup = $productos ?? null;

                    // Pasamos los relacionados a la variable que usa la parcial
                    $productos = $relatedProducts;

                    // Incluimos la parcial (ruta relativa desde views/producto/ -> ../home/_products_grid.php)
                    include __DIR__ . '/../home/_products_grid.php';

                    // Restauramos el estado anterior (evitar side-effects)
                    if ($__productos_backup === null) {
                        unset($productos);
                    } else {
                        $productos = $__productos_backup;
                    }
                    ?>
                </div>
            <?php else: ?>
                <p>No hay productos relacionados para mostrar.</p>
            <?php endif; ?>
        </section>




        <!-- Reviews -->
        <h3 class="text-xl font-semibold mt-6 mb-3">Reseñas de clientes</h3>
        <?php if (empty($reviews)): ?>
            <p class="text-gray-500">Todavía no hay reseñas para este producto.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="border-b py-3">
                    <div class="flex items-center gap-2">
                        <strong><?= htmlspecialchars($review['usuario_nombre']) ?></strong>
                        <span class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <div>
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <span class="<?= $i <= $review['puntuacion'] ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="font-medium"><?= htmlspecialchars($review['titulo']) ?></p>
                    <p><?= htmlspecialchars($review['texto']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

</body>

</html>