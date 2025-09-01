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

<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/home.css') ?>">
<link rel="stylesheet" href="<?= url('css/cards.css') ?>">

<body>
    <div class="header-container">
        <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    </div>

    <div class="main-container">
        <div class="content-wrapper">

            <!-- ================================================================== -->
            <!-- BREADCRUMB                                                         -->
            <!-- ================================================================== -->
            <nav class="breadcrumb">
                <a href="<?= url('home') ?>">INICIO</a> /
                <?php if (!empty($categoriaActual)): ?>
                    <?= htmlspecialchars(strtoupper($categoriaActual['nombre'])) ?>
                <?php else: ?>
                    ELECTRÓNICOS
                <?php endif; ?>
            </nav>

            <!-- ================================================================== -->
            <!-- TÍTULO DE LA PÁGINA                                               -->
            <!-- ================================================================== -->
            <div class="page-header">
                <?php if (!empty($termino)): ?>
                    <h1 class="page-title">Resultados para: "<?= htmlspecialchars($termino, ENT_QUOTES, 'UTF-8') ?>"</h1>
                    <p class="page-subtitle">
                        <?= $totalEncontrados ?> productos encontrados
                    </p>
                <?php elseif (!empty($categoriaActual)): ?>
                    <h1 class="page-title"><?= htmlspecialchars($categoriaActual['nombre'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="page-subtitle">Mostrando productos <?= ($paginaActual - 1) * $productosPorPagina + 1 ?>-<?= min($paginaActual * $productosPorPagina, $totalEncontrados) ?> de <?= $totalEncontrados ?> en total</p>
                <?php else: ?>
                    <?php
                    // Obtener el nombre de la categoría seleccionada desde el filtro
                    $categoriaNombre = 'Electrónicos'; // Valor por defecto
                    if (!empty($_GET['categoria']) && !empty($categoriasDisponibles)) {
                        foreach ($categoriasDisponibles as $categoria) {
                            if ($categoria['id'] == $_GET['categoria']) {
                                $categoriaNombre = $categoria['nombre'];
                                break;
                            }
                        }
                    }
                    ?>
                    <h1 class="page-title"><?= htmlspecialchars($categoriaNombre, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="page-subtitle">Mostrando productos <?= ($paginaActual - 1) * $productosPorPagina + 1 ?>-<?= min($paginaActual * $productosPorPagina, $totalEncontrados) ?> de <?= $totalEncontrados ?> en total</p>
                <?php endif; ?>

            </div>

            <!-- Productos -->
            <div class="products-content">
                <div id="productsWrapper">
                    <div class="products-grid">
                        <?php if (!empty($resultados)): ?>
                            <?php foreach ($resultados as $producto): ?>
                                <?php
                                    $precioFinal   = isset($producto['precio']) ? (float)$producto['precio'] : 0.0;
                                    $precioTachado = (isset($producto['precio_tachado']) && $producto['precio_tachado'] !== '') ? (float)$producto['precio_tachado'] : null;
                                    $showTachado = ($precioTachado !== null && $precioTachado > $precioFinal) && !empty($producto['precio_tachado_visible']);
                                    
                                    // Calcular porcentaje de descuento si hay precio tachado
                                    $porcentajeDescuento = 0;
                                    if ($showTachado && $precioTachado > 0) {
                                        $porcentajeDescuento = round((($precioTachado - $precioFinal) / $precioTachado) * 100);
                                    }
                                    
                                    $imgSrc = url('uploads/default-product.png');
                                    if (!empty($producto['imagen'])) {
                                        $imgSrc = url('uploads/' . $producto['imagen']);
                                    }
                                ?>
                                <div class="product-card <?= !empty($producto['destacado']) ? 'is-featured' : '' ?>">
                                    <?php if (!empty($producto['destacado'])): ?>
                                        <div class="badge-featured">★</div>
                                    <?php endif; ?>
                                    
                                    <?php if ($showTachado && $porcentajeDescuento > 0): ?>
                                        <div class="badge-porcentaje">-<?= $porcentajeDescuento ?>%</div>
                                    <?php endif; ?>

                                    <a href="<?= url('producto/ver/' . $producto['id']) ?>" class="product-link">
                                        <div class="product-image-container">
                                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                            <p class="product-description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                            <div class="product-price">
                                                <span class="price-now">S/ <?= number_format($precioFinal, 2) ?></span>
                                                <?php if ($showTachado): ?>
                                                    <span class="price-old">S/ <?= number_format($precioTachado, 2) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>

                                    <form method="POST" action="<?= url('carrito/agregar') ?>" class="add-to-cart-form" onClick="event.stopPropagation();">
                                        <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                        <div class="quantity-section">
                                            <input type="number" name="cantidad" value="1" min="1" class="quantity-input">
                                            <button type="submit" class="add-button">Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">🔍</div>
                                <h3 class="empty-title">No se encontraron resultados</h3>
                                <p class="empty-description">Intenta con otra palabra clave.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
</body>

</html>