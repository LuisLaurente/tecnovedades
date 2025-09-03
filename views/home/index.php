<?php
$metaTitle = "Bienvenido a BYTEBOX - Tecnología y Novedades";
$metaDescription = "Descubre lo último en tecnología, novedades y accesorios al mejor precio.";


$metaTitle = "Bytebox - Tu Tienda de Tecnología y Componentes";
$metaDescription = "Descubre lo último en tecnología, componentes de PC, periféricos y más. Calidad y confianza en cada compra.";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// --- Fallback para categorías (Mantenido de tu código) ---
if (!isset($categorias)) {
  try {
    if (class_exists('\Models\Categoria')) {
      $categorias = method_exists('\Models\Categoria', 'obtenerPadres')
        ? \Models\Categoria::obtenerPadres()
        : [];
    } else {
      $categorias = [];
    }
  } catch (\Throwable $e) {
    error_log('[home/index] Error cargando categorias: ' . $e->getMessage());
    $categorias = [];
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

  <!-- Estilos -->
  <link rel="stylesheet" href="<?= url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= url('css/cards.css') ?>"> <!-- No se toca -->

  <!-- Iconos y fuentes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">

  <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
  <title><?= htmlspecialchars($metaTitle) ?></title>
</head>

<body>
  <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

  <main>
    <!-- ===================== HERO + CATEGORÍAS (UN CONTENEDOR 100vh) ===================== -->
    <section class="hero-and-categories">
      <div class="hero-banner-container">
        <?php if (!empty($banners)): ?>
          <?php foreach ($banners as $index => $ban): ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>">
              <img src="<?= url('uploads/banners/' . htmlspecialchars($ban['nombre_imagen'])) ?>" alt="Banner <?= $index + 1 ?>">
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="hero-content-static">
            <div class="hero-text">
              <h1 class="fade-text">GIRA, AJUSTA Y CREA 2 <span class="highlight">EL MONITOR PERFECTO</span></h1>
              <p class="fade-text">Encuentra la configuración ideal para tu espacio de trabajo o gaming con nuestra selección de monitores de alto rendimiento.</p>
            </div>
            <div class="hero-image">
              <img src="https://i.imgur.com/gYf2xS5.png" alt="Monitor Gamer de alto rendimiento">
            </div>
          </div>
        <?php endif; ?>
      </div>


    </section>
          <!-- Contenedor de categorías pegado al pie del hero (permanecerá dentro del mismo contenedor 100vh) -->
      <div class="categories-carousel-container">
        <div class="container">
          <div class="section-title">
            <h2 class="fade-text">Categorías</h2>
            <div class="line"></div>
          </div>

          <div class="categories-carousel-track" aria-label="Carrusel de categorías">
            <?php if (!empty($categorias)): ?>
              <?php foreach ($categorias as $cat): ?>
                <?php
                $catId = $cat['id'] ?? '';
                $catName = htmlspecialchars($cat['nombre'] ?? 'Categoría');
                $catLink = $catId !== '' ? url('home/busqueda?categoria=' . $catId) : '#';
                $imgFile = $cat['imagen'] ?? $cat['nombre_imagen'] ?? $cat['imagen_categoria'] ?? null;
                $imgSrc = $imgFile ? url('uploads/categorias/' . $imgFile) : url('uploads/default-category.png');
                ?>
                <a class="category-box" href="<?= $catLink ?>" aria-label="<?= $catName ?>">
                  <div class="category-image"><img src="<?= $imgSrc ?>" alt="<?= $catName ?>"></div>
                  <div class="category-name"><?= $catName ?></div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="text-align:center;">No hay categorías para mostrar.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <!-- ===================== FIN HERO + CATEGORÍAS ===================== -->
    <!-- PRODUCTOS DESTACADOS (CARRUSEL INFINITO) -->
    <section class="featured-products">
      <div class="container">
        <div class="section-title">
          <h2>Nuestros Destacados</h2>
          <div class="line"></div>
        </div>
      </div>
      <!-- Contenedor del carrusel de productos -->
      <div class="products-carousel-container" aria-label="Carrusel de productos destacados">
        <?php
        if (!empty($productos)) {
          // Tu parcial _products_grid.php se incluye aquí. El JS lo manipulará.
          include __DIR__ . '/_products_grid.php';
        } else {
          echo '<p style="text-align:center;">No hay productos destacados disponibles.</p>';
        }
        ?>
      </div>
    </section>

    <!-- WHY CHOOSE US -->
    <section class="why-choose-us">
      <div class="container">
        <div class="section-title">
          <h2>¿Por qué elegir Bytebox?</h2>
          <div class="line"></div>
        </div>
        <div class="features-grid">
          <div class="feature-box">
            <i class="fa-solid fa-shield-halved"></i>
            <h3>Calidad Garantizada</h3>
            <p>Seleccionamos los mejores componentes y periféricos de marcas líderes para asegurar tu satisfacción y rendimiento.</p>
          </div>
          <div class="feature-box">
            <i class="fa-solid fa-headset"></i>
            <h3>Soporte Técnico Experto</h3>
            <p>Nuestro equipo está listo para ayudarte con cualquier consulta, desde la compatibilidad de piezas hasta la configuración de tu equipo.</p>
          </div>
          <div class="feature-box">
            <i class="fa-solid fa-truck-fast"></i>
            <h3>Envíos a Nivel Nacional</h3>
            <p>Recibe tus productos de forma rápida y segura en la puerta de tu casa, sin importar en qué parte del país te encuentres.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

  <!-- SCRIPTS -->
  <script>
    // --- Banner simple fade ---
    (function() {
      const slides = document.querySelectorAll('.hero-slide');
      if (slides.length <= 1) return;
      let currentSlide = 0;

      function showNextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
      }
      setInterval(showNextSlide, 12500);
    })();

    // --- Lógica de Carrusel Infinito (Reutilizable) ---
    function setupInfiniteCarousel(containerSelector, trackSelector, itemsSelector) {
      const container = document.querySelector(containerSelector);
      if (!container) return;
      const track = container.querySelector(trackSelector);
      if (!track) return;

      const items = Array.from(track.children);
      if (items.length === 0) return;

      // Clonar elementos para el bucle
      items.forEach(item => {
        const clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
      });

      // Añadir clase para activar animación CSS
      track.classList.add('scrolling');
    }

    // --- Funcionalidad de Arrastre para Productos (Versión Simplificada) ---
    function setupDragCarousel(containerSelector) {
      const container = document.querySelector(containerSelector);
      if (!container) return;
      
      const track = container.querySelector('.products-grid');
      if (!track) return;

      // Clonar elementos para scroll infinito
      const originalItems = Array.from(track.children);
      if (originalItems.length > 0) {
        originalItems.forEach(item => {
          const clone = item.cloneNode(true);
          clone.setAttribute('aria-hidden', 'true');
          track.appendChild(clone);
        });
      }

      let isDragging = false;
      let startX = 0;
      let scrollLeft = 0;
      let currentX = 0;

      // Configuración inicial
      track.style.animation = 'none';
      track.style.transform = 'translateX(0px)';

      // Mouse events
      container.addEventListener('mousedown', (e) => {
        isDragging = true;
        container.classList.add('dragging');
        track.style.transition = 'none';
        track.style.animation = 'none';
        
        startX = e.pageX - container.offsetLeft;
        scrollLeft = currentX;
      });

      document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.2;
        currentX = scrollLeft + walk;
        
        // Límites básicos
        const maxScroll = 0;
        const minScroll = -(track.scrollWidth - container.offsetWidth);
        
        if (currentX > maxScroll) {
          currentX = maxScroll + (currentX - maxScroll) * 0.3;
        }
        if (currentX < minScroll) {
          currentX = minScroll + (currentX - minScroll) * 0.3;
        }
        
        track.style.transform = `translateX(${currentX}px)`;
      });

      document.addEventListener('mouseup', () => {
        if (!isDragging) return;
        isDragging = false;
        container.classList.remove('dragging');
        
        // Aplicar límites finales
        const maxScroll = 0;
        const minScroll = -(track.scrollWidth - container.offsetWidth);
        
        if (currentX > maxScroll) currentX = maxScroll;
        if (currentX < minScroll) currentX = minScroll;
        
        track.style.transition = 'transform 0.3s ease-out';
        track.style.transform = `translateX(${currentX}px)`;
        
        // Reactivar scroll automático después de un tiempo
        setTimeout(restartAutoScroll, 3000);
      });

      // Touch events
      container.addEventListener('touchstart', (e) => {
        isDragging = true;
        container.classList.add('dragging');
        track.style.transition = 'none';
        track.style.animation = 'none';
        
        startX = e.touches[0].pageX - container.offsetLeft;
        scrollLeft = currentX;
      }, { passive: true });

      container.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        
        const x = e.touches[0].pageX - container.offsetLeft;
        const walk = (x - startX) * 1.2;
        currentX = scrollLeft + walk;
        
        // Límites básicos
        const maxScroll = 0;
        const minScroll = -(track.scrollWidth - container.offsetWidth);
        
        if (currentX > maxScroll) {
          currentX = maxScroll + (currentX - maxScroll) * 0.3;
        }
        if (currentX < minScroll) {
          currentX = minScroll + (currentX - minScroll) * 0.3;
        }
        
        track.style.transform = `translateX(${currentX}px)`;
      }, { passive: false });

      container.addEventListener('touchend', () => {
        if (!isDragging) return;
        isDragging = false;
        container.classList.remove('dragging');
        
        // Aplicar límites finales
        const maxScroll = 0;
        const minScroll = -(track.scrollWidth - container.offsetWidth);
        
        if (currentX > maxScroll) currentX = maxScroll;
        if (currentX < minScroll) currentX = minScroll;
        
        track.style.transition = 'transform 0.3s ease-out';
        track.style.transform = `translateX(${currentX}px)`;
        
        // Reactivar scroll automático después de un tiempo
        setTimeout(restartAutoScroll, 3000);
      });

      function restartAutoScroll() {
        if (isDragging) return;
        
        // Volver al inicio suavemente
        track.style.transition = 'transform 1s ease-in-out';
        track.style.transform = 'translateX(0px)';
        currentX = 0;
        
        // Reactivar animación CSS
        setTimeout(() => {
          if (!isDragging) {
            track.style.transition = '';
            track.style.animation = 'scroll-infinite 60s linear infinite';
          }
        }, 1000);
      }

      // Prevenir comportamientos por defecto
      container.addEventListener('dragstart', e => e.preventDefault());
      container.addEventListener('selectstart', e => e.preventDefault());
      
      // Prevenir clicks durante arrastre
      container.addEventListener('click', (e) => {
        if (Math.abs(currentX - scrollLeft) > 5) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      // Iniciar scroll automático
      setTimeout(() => {
        if (!isDragging) {
          track.style.animation = 'scroll-infinite 60s linear infinite';
        }
      }, 500);
    }

    // --- Inicializar carruseles ---
    document.addEventListener('DOMContentLoaded', function() {
      // Solo configurar funcionalidad de arrastre (que incluye la animación automática)
      setupDragCarousel('.products-carousel-container');
    });
  </script>
</body>

</html>