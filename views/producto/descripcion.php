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
    'nombre' => 'Smartwatch Samsung Galaxy Watch6',
    'descripcion' => 'Funda personalizada: Personaliza la pantalla. Actualización instantánea: Cambia el color. Protección Segura: Además de innovador. Diseño Delgado y Ligero: Su diseño es excepcional y muy cómodo para el día a día, te olvidarás que lo llevas puesto.',
    'descripcion_larga' => 'El Samsung Galaxy Watch6 es un smartwatch elegante y potente diseñado para quienes buscan estilo y funcionalidad en su muñeca. Incorpora una pantalla Super AMOLED de 1.5" con excelente resolución y Always-On Display. Con Wear OS Powered by Samsung, ofrece acceso a apps, notificaciones y servicios inteligentes. Cuenta con sensores avanzados para salud, fitness, y bienestar, incluyendo monitoreo de ritmo cardíaco, SpO2 y sueño. Su batería de larga duración, resistencia al agua 5 ATM y diseño premium lo convierten en un compañero ideal para el día a día.',
    'precio' => 1599.00,
    'precio_tachado' => 1899.00,
    'porcentaje_descuento' => 16,
    'precio_tachado_visible' => 1,
    'porcentaje_visible' => 1,
    'imagenes' => [['nombre_imagen' => 'default-product.png']],
    'categorias' => [],
    'stock' => 25,
    'especificaciones_array' => [
        'Pantalla: Super AMOLED de 1.5" (37.3 mm)',
        'Resolución: 480 x 480 píxeles',
        'Color Depth: 16 millones de colores',
        'Procesador: Dual-Core a 1.4 GHz',
        'Sistema Operativo: Wear OS Powered by Samsung',
        'Memoria RAM: 2 GB',
        'Almacenamiento: 16 GB',
        'Batería: 425 mAh',
        'Conectividad: Bluetooth 5.3, Wi-Fi, NFC, GPS',
        'Sensores: Acelerómetro, Barómetro, Giroscopio, Sensor de Luz, Sensor de Ritmo Cardíaco'
    ]
];

// Preparar precios y flags
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
$breadcrumb = $breadcrumb ?? ($producto['breadcrumb'] ?? ['Inicio', 'Tecnología', 'Smartwatch Samsung Galaxy Watch6']);

// Related products & reviews fallback
$relatedProducts = $relatedProducts ?? [];
$reviews = $reviews ?? [];

// helper para obtener imagen
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
    <link rel="stylesheet" href="<?= url('css/producto-descripcion.css') ?>">
    <link rel="stylesheet" href="<?= url('css/cards.css') ?>">
</head>

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    <main class="product-detail-container">
        <div class="bread-links">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
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
                        <li class="breadcrumb-item crumb-current" aria-current="page">Productos</li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>

        <section class="product-info-grid">
            <div class="product-image-gallery">
                <img id="main-product-image" src="<?= producto_imagen_url($producto, 0) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="main-product-image">
                <?php if (!empty($producto['imagenes']) && count($producto['imagenes']) > 1): ?>
                    <div class="thumbnail-images" role="list">
                        <?php foreach ($producto['imagenes'] as $idx => $img):
                            $imgUrl = url('uploads/' . $img['nombre_imagen']);
                        ?>
                            <img class="thumb <?= $idx === 0 ? 'activo' : '' ?>" src="<?= $imgUrl ?>" data-src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($producto['nombre']) ?> miniatura <?= $idx + 1 ?>" role="listitem">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-short-description">
                <h2>Descripción:</h2>
                <!-- CORRECCIÓN: Se añade la clase 'short-desc-text' para limitar las líneas con CSS -->
                <p class="short-desc-text">
                    <?= htmlspecialchars($producto['descripcion'] ?? 'No hay descripción disponible.') ?>
                </p>
                <a href="#descripcion-section" class="read-more-link">Leer más</a>

                <div class="info-boxes">
                    <div class="info-box">
                        <img src="<?= url('images/delivery_icon.png') ?>" alt="Envíos">
                        <span>Envíos a todo el Perú</span>
                    </div>
                    <div class="info-box">
                        <img src="<?= url('images/warranty_icon.png') ?>" alt="Garantía">
                        <span>Garantía en tus pedidos</span>
                    </div>
                    <div class="info-box">
                        <img src="<?= url('images/secure_payment_icon.png') ?>" alt="Pagos Seguros">
                        <span>Pagos 100% seguros</span>
                    </div>
                </div>
            </div>

            <div class="product-details">
                <h1><?= htmlspecialchars($producto['nombre']) ?></h1>

                <div class="rating">
                    <a href="#reviews-section" class="rating-link">
                        <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
                        <span class="rating-count">(<?= isset($producto['rating_count']) ? (int)$producto['rating_count'] : '0' ?>)</span>
                    </a>
                </div>

                <div class="price">
                    <span class="current-price">S/ <?= number_format($precioFinal, 2) ?></span>
                    <?php if ($showTachado): ?>
                        <span class="old-price">S/ <?= number_format($precioTachado, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($showPct): ?>
                        <span class="discount">-<?= number_format($descuentoPct, 0) ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="quantity-selector" aria-label="Seleccionar cantidad">
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
            </div>
        </section>

        <section id="descripcion-section" class="collapsible-section">
            <h2 class="collapsible-header active">Descripción <span class="arrow">&#9650;</span></h2>
            <div class="collapsible-content" style="display: block;">
                <?php if (!empty($producto['descripcion_larga'])): ?>
                    <?= nl2br(htmlspecialchars($producto['descripcion_larga'])) ?>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($producto['descripcion'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="collapsible-section partially-visible">
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
                <button class="view-specs-toggle view-more-specs">VER MÁS ▼</button>
                <button class="view-specs-toggle view-less-specs">VER MENOS ▲</button>
            </div>
        </section>

        <section class="related-products">
            <h2>Productos Relacionados</h2>
            <?php if (!empty($relatedProducts) && is_array($relatedProducts)): ?>
                <div class="products-carousel-container" aria-label="Carrusel de productos relacionados">
                    <?php
                    $__productos_backup = $productos ?? null;
                    $productos = $relatedProducts;
                    include __DIR__ . '/../home/_products_grid.php';
                    if ($__productos_backup === null) unset($productos);
                    else $productos = $__productos_backup;
                    ?>
                </div>
            <?php else: ?>
                <p>No hay productos relacionados para mostrar.</p>
            <?php endif; ?>
<!-- ================================================== -->
<!-- INICIO DE LA NUEVA SECCIÓN DE RESEÑAS CON ESTADÍSTICAS -->
<!-- ================================================== -->
<section id="reviews-section" class="reviews-container">
    <h2 class="reviews-main-title">Comentarios de este producto</h2>

    <?php
    // --- Bloque de cálculo de estadísticas ---
    $totalReviews = count($reviews);
    $averageRating = 0;
    $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    if ($totalReviews > 0) {
        $totalScore = 0;
        foreach ($reviews as $review) {
            $puntuacion = (int)$review['puntuacion'];
            if (isset($ratingCounts[$puntuacion])) {
                $ratingCounts[$puntuacion]++;
            }
            $totalScore += $puntuacion;
        }
        $averageRating = round($totalScore / $totalReviews, 1);
    }
    // --- Fin del bloque de cálculo ---
    ?>

    <?php if ($totalReviews > 0): ?>
        <div class="reviews-summary">
            <!-- Columna Izquierda: Puntuación General -->
            <div class="overall-rating">
                <div class="score">
                    <span class="score-number"><?= htmlspecialchars($averageRating) ?></span>/5
                </div>
                <div class="stars-display">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= floor($averageRating) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <div class="total-reviews-count"><?= $totalReviews ?> comentario<?= $totalReviews > 1 ? 's' : '' ?></div>
            </div>

            <!-- Columna Derecha: Desglose de Puntuaciones -->
            <div class="rating-breakdown">
                <?php foreach ($ratingCounts as $star => $count): ?>
                    <?php
                    $percentage = ($totalReviews > 0) ? ($count / $totalReviews) * 100 : 0;
                    ?>
                    <div class="breakdown-row">
                        <span class="star-label"><?= $star ?> ★</span>
                        <div class="bar-container">
                            <div class="bar" style="width: <?= $percentage ?>%;"></div>
                        </div>
                        <span class="count-label"><?= $count ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Listado de Reseñas Individuales -->
    <div class="individual-reviews">
        <?php if ($totalReviews > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <h3 class="review-title"><?= htmlspecialchars($review['titulo']) ?></h3>
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= $review['puntuacion'] ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-author-date">
                        <span class="review-author">por <?= htmlspecialchars($review['usuario_nombre']) ?></span>
                        <span class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p class="review-text"><?= nl2br(htmlspecialchars($review['texto'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-reviews-message">Todavía no hay comentarios para este producto.</p>
        <?php endif; ?>
    </div>
</section>
<!-- ================================================ -->
<!-- FIN DE LA NUEVA SECCIÓN DE RESEÑAS -->
<!-- ================================================ -->

    </main>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

    <!-- CORRECCIÓN: Script completo con la lógica corregida para los desplegables -->
    <script>
        (function() {
            // Galería
            const mainImage = document.getElementById('main-product-image');
            const thumbs = document.querySelectorAll('.thumbnail-images .thumb');
            if (mainImage && thumbs.length > 0) {
                thumbs.forEach(t => {
                    t.addEventListener('click', function() {
                        mainImage.src = this.dataset.src || this.src;
                        thumbs.forEach(x => x.classList.remove('activo'));
                        this.classList.add('activo');
                    });
                });
            }

            // Cantidad
            const qtyInput = document.getElementById('qty-input');
            const qtyIncrease = document.getElementById('qty-increase');
            const qtyDecrease = document.getElementById('qty-decrease');
            const formCantidad = document.getElementById('form-cantidad');
            const stockLimit = <?= isset($producto['stock']) && $producto['stock'] !== null ? (int)$producto['stock'] : 'null' ?>;

            function setQty(val) {
                val = Math.max(1, Math.floor(parseInt(val) || 1));
                if (stockLimit !== null) val = Math.min(val, stockLimit);
                qtyInput.value = val;
                if (formCantidad) formCantidad.value = val;
            }

            if (qtyIncrease) qtyIncrease.addEventListener('click', () => setQty(parseInt(qtyInput.value) + 1));
            if (qtyDecrease) qtyDecrease.addEventListener('click', () => setQty(parseInt(qtyInput.value) - 1));
            if (qtyInput) qtyInput.addEventListener('change', () => setQty(qtyInput.value));
            if (formCantidad) formCantidad.value = qtyInput ? qtyInput.value : '1';

            // Collapsibles (para Descripción)
            document.querySelectorAll('.collapsible-header').forEach(header => {
                header.addEventListener('click', () => {
                    const section = header.closest('.collapsible-section');
                    // Si es la de especificaciones y está parcialmente visible, no hacer nada aquí
                    if (section.classList.contains('partially-visible')) {
                        return;
                    }

                    header.classList.toggle('active');
                    const content = header.nextElementSibling;
                    const arrow = header.querySelector('.arrow');

                    if (header.classList.contains('active')) {
                        content.style.display = 'block';
                        if (arrow) arrow.innerHTML = '&#9650;';
                    } else {
                        content.style.display = 'none';
                        if (arrow) arrow.innerHTML = '&#9660;';
                    }
                });
            });

            // Lógica para expandir especificaciones
            const specsSection = document.querySelector('.partially-visible');
            if (specsSection) {
                const viewMoreButton = specsSection.querySelector('.view-more-specs');
                const viewLessButton = specsSection.querySelector('.view-less-specs');
                const header = specsSection.querySelector('.collapsible-header');
                const content = specsSection.querySelector('.collapsible-content');

                const expandSpecs = function(e) {
                    if (specsSection.classList.contains('partially-visible')) {
                        specsSection.classList.remove('partially-visible');
                        if (!header.classList.contains('active')) {
                            header.classList.add('active');
                        }
                        if (content) content.style.display = 'block';
                        const arrow = header.querySelector('.arrow');
                        if(arrow) arrow.innerHTML = '&#9650;';
                        if (e) e.stopPropagation();
                    }
                };

                const collapseSpecs = function(e) {
                    if (!specsSection.classList.contains('partially-visible')) {
                        specsSection.classList.add('partially-visible');
                        if (header.classList.contains('active')) {
                            header.classList.remove('active');
                        }
                        if (content) content.style.display = '';
                        const arrow = header.querySelector('.arrow');
                        if(arrow) arrow.innerHTML = '&#9660;';
                        if (e) e.stopPropagation();
                    }
                };

                if (viewMoreButton) {
                    viewMoreButton.addEventListener('click', expandSpecs);
                }
                if (viewLessButton) {
                    viewLessButton.addEventListener('click', collapseSpecs);
                }
                if (header) {
                    header.addEventListener('click', expandSpecs, true);
                }
            }

            // Enlaces con desplazamiento suave (smooth scroll)
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

        })();
    </script>
</body>

</html>