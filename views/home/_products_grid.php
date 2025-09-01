<?php
// views/home/_products_grid.php
// Variables esperadas: $productos
?>
<div id="productsWrapper">
  <div class="products-grid">
    <?php if (!empty($productos)): ?>
      <?php foreach ($productos as $producto): ?>
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
          if (!empty($producto['imagenes']) && !empty($producto['imagenes'][0]['nombre_imagen'])) {
              $imgSrc = url('uploads/' . $producto['imagenes'][0]['nombre_imagen']);
          } elseif (!empty($producto['imagen'])) {
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
              <button type="submit" class="add-button"> Agregar</button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3 class="empty-title">No hay productos disponibles</h3>
        <p class="empty-description">Aún no se han agregado productos visibles al catálogo.</p>
      </div>
    <?php endif; ?>
  </div>
</div>