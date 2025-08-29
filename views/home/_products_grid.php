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
          
          // Asegurar que la descripción no esté vacía
          $descripcion = !empty($producto['descripcion']) ? $producto['descripcion'] : 'Producto de calidad disponible en nuestra tienda.';
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
              <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" loading="lazy">
            </div>
            <div class="product-info">
              <h3 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
              <p class="product-description"><?= htmlspecialchars($descripcion) ?></p>
              <div class="product-price">
                <span class="price-now">S/ <?= number_format($precioFinal, 2) ?></span>
                <?php if ($showTachado): ?>
                  <span class="price-old">S/ <?= number_format($precioTachado, 2) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </a>

          <!-- Formulario de agregar al carrito (funciona sin login) -->
          <form method="POST" action="<?= url('carrito/agregar') ?>" class="add-to-cart-form" onclick="event.stopPropagation();">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="quantity-section">
              <input type="number" name="cantidad" value="1" min="1" max="99" class="quantity-input" title="Cantidad">
              <button type="submit" class="add-button" title="Agregar al carrito">
                <i class="fas fa-cart-plus"></i> Agregar
              </button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3 class="empty-title">No hay productos disponibles</h3>
        <p class="empty-description">
          <?php if (!empty($_GET['termino']) || !empty($_GET['categoria']) || !empty($_GET['min_price']) || !empty($_GET['max_price'])): ?>
            No se encontraron productos que coincidan con los filtros aplicados. Intenta ajustar los criterios de búsqueda.
          <?php else: ?>
            Aún no se han agregado productos visibles al catálogo. Vuelve pronto para ver nuestras novedades.
          <?php endif; ?>
        </p>
        <div class="empty-actions">
          <a href="<?= url('home/busqueda') ?>" class="clear-filters-btn">Limpiar Filtros</a>
          <a href="<?= url('home') ?>" class="back-home-btn">Volver al Inicio</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Script para mejorar la experiencia del usuario
document.addEventListener('DOMContentLoaded', function() {
    // Manejar formularios de agregar al carrito
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = this.querySelector('.add-button');
            const originalText = button.innerHTML;
            
            // Mostrar estado de carga
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';
            button.disabled = true;
            
            // Restaurar el botón después de un tiempo (para feedback visual)
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        });
    });
    
    // Lazy loading para imágenes (si no está soportado nativamente)
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});
</script>

