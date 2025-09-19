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
        <div class="section-title" id="categorias-section">
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
    <!-- ===================== PRODUCTOS DESTACADOS (CARRUSEL INFINITO) ===================== -->
 
        <section class="featured-products">
      <div class="container">
        <div class="section-title">
          <h2>Productos Destacados</h2>
          <div class="line"></div>
        </div>
      </div>
      <!-- Contenedor del carrusel de productos -->
      <div class="products-carousel-container" aria-label="Carrusel de productos destacados">
        <?php
        if (!empty($productos_destacados)) {  // Cambio 1: Usa el nombre del controlador ($productos_destacados)
          $productos = $productos_destacados;  // Cambio 2: Asigna a $productos para compatibilidad con el parcial _products_grid.php
          // Ahora el include recibirá $productos con los datos reales
          include __DIR__ . '/_products_grid.php';
        } else {
          echo '<p style="text-align:center;">No hay productos destacados disponibles.</p>';  // Fallback si no hay datos
        }
        ?>
      </div>
    </section>
    <!-- ... (código anterior) ... -->
    <!-- ================================================== -->
    <!--          INICIO DE LA SECCIÓN DE BANNERS SECUNDARIOS          -->
    <!-- ================================================== -->
    <section class="promo-banners-section">
      <div class="container">
        <div class="banners-grid">
          <?php
          $banner_sec_izq = !empty($banners_secundarios_izquierda) ? $banners_secundarios_izquierda[0] : null;
          $banner_sec_der = !empty($banners_secundarios_derecha) ? $banners_secundarios_derecha[0] : null;
          ?>

          <?php if ($banner_sec_izq): ?>
            <a href="#" class="promo-banner-item">
              <img src="<?= url("uploads/banners/" . htmlspecialchars($banner_sec_izq["nombre_imagen"])) ?>" alt="Banner Secundario Izquierda">
            </a>
          <?php else: ?>
            <!-- Fallback si no hay banner secundario izquierdo en la BD -->
            <a href="#" class="promo-banner-item">
              <img src="<?= url("images/baner1.jpg") ?>" alt="Promoción o categoría destacada 1">
            </a>
          <?php endif; ?>

          <?php if ($banner_sec_der): ?>
            <a href="#" class="promo-banner-item">
              <img src="<?= url("uploads/banners/" . htmlspecialchars($banner_sec_der["nombre_imagen"])) ?>" alt="Banner Secundario Derecha">
            </a>
          <?php else: ?>
            <!-- Fallback si no hay banner secundario derecho en la BD -->
            <a href="#" class="promo-banner-item">
              <img src="<?= url("images/baner2.jpg") ?>" alt="Promoción o categoría destacada 2">
            </a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- ================================================== -->
    <!--           FIN DE LA SECCIÓN DE BANNERS             -->
    <!-- ================================================== -->
    <!-- ... (código posterior) ... -->
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
            <h3>Calidad Premium</h3>
            <p>Garantizamos productos seleccionados bajo estrictos estándares, diseñados para ofrecerte el máximo rendimiento y una experiencia de compra superior.</p>
          </div>
          <div class="feature-box">
            <i class="fa-solid fa-headset"></i>
            <h3>Soporte Postventa</h3>
            <p>Nuestro equipo especializado te brinda asistencia continua después de tu compra, resolviendo dudas y asegurando el mejor desempeño de tus equipos.</p>
          </div>
          <div class="feature-box">
            <i class="fa-solid fa-truck-fast"></i>
            <h3>Envíos a Nivel Nacional</h3>
            <p>Realizamos entregas rápidas y seguras, con un tiempo de envío de hasta 24 horas en Lima y de 1 a 2 días en provincias, asegurando puntualidad y confianza en cada pedido.</p>
          </div>
        </div>

      </div>
    </section>
  </main>

  <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

  <!-- SCRIPTS -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
    const targetId = "categorias-section";
    const target = document.getElementById(targetId);

    // 👉 1. Interceptar clics en el link dentro de la misma página
    const link = document.querySelector(`a[href$="#${targetId}"]`);
    if (link && target) {
      link.addEventListener("click", function(e) {
        e.preventDefault();
        target.scrollIntoView({
          behavior: "smooth",
          block: "center"
        });
        // Actualizar hash en la URL sin que el navegador haga scroll automático
        history.pushState(null, "", `#${targetId}`);
      });
    }

    // 👉 2. Si vienes desde otra página con hash (#categorias-section)
    if (window.location.hash === `#${targetId}` && target) {
      // Esperar un momento a que cargue todo antes de centrar
      setTimeout(() => {
        target.scrollIntoView({
          behavior: "smooth",
          block: "center"
        });
      }, 300); // puedes ajustar el delay
    }
  });
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

    // --- Funcionalidad de Arrastre para Productos con Scroll Infinito Seamless ---
    function setupDragCarousel(containerSelector) {
      const container = document.querySelector(containerSelector);
      if (!container) return;

      const track = container.querySelector('.products-grid');
      if (!track) return;

      const originalItems = Array.from(track.children);
      if (originalItems.length === 0) return;

      // PASO 1: Clonar items para loop infinito (2 veces -> 3 sets totales)
      for (let i = 0; i < 2; i++) {
        originalItems.forEach(item => {
          const clone = item.cloneNode(true);
          clone.setAttribute('aria-hidden', 'true');
          track.appendChild(clone);
        });
      }

      // PASO 2: Calcular dimensiones
      let itemWidth = 0;
      let setWidth = 0; // Ancho de un set original
      let totalSets = 3; // Original + 2 clones
      let isDragging = false;
      let isAnimating = true;
      let startX = 0;
      let currentX = 0;
      let initialX = 0;

      function calculateDimensions() {
        if (originalItems.length === 0) return;
        const firstItem = originalItems[0];
        const style = window.getComputedStyle(firstItem);
        const marginRight = parseFloat(style.marginRight) || 0;
        const gap = parseFloat(getComputedStyle(track).gap) || 0;
        itemWidth = firstItem.offsetWidth + marginRight;

        // Calcula ancho del set original (items + gaps)
        setWidth = itemWidth * originalItems.length + gap * (originalItems.length - 1);
        track.style.width = `${setWidth * totalSets}px`; // Total 3 sets
      }

      calculateDimensions();
      window.addEventListener('resize', () => {
        calculateDimensions();
        if (!isDragging) {
          currentX = 0;
          track.style.transform = 'translateX(0)';
          track.classList.add('scrolling');
          isAnimating = true;
        }
      });

      // PASO 3: Configuración inicial
      track.style.transform = 'translateX(0)';
      setTimeout(() => {
        if (!isDragging && !track.classList.contains('scrolling')) {
          track.classList.add('scrolling');
          isAnimating = true;
        }
      }, 500);

      // PASO 4: Mouse events
      container.addEventListener('mousedown', (e) => {
        if (isDragging) return;
        isDragging = true;
        container.classList.add('dragging');
        track.classList.remove('scrolling');
        isAnimating = false;
        track.style.transition = 'none';

        initialX = currentX;
        startX = e.pageX - container.offsetLeft;
        e.preventDefault();
      });

      document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.2; // Sensibilidad
        currentX = initialX + walk;

        // Loop infinito: mantener currentX en rango [-setWidth, 0]
        while (currentX > 0) currentX -= setWidth;
        while (currentX < -setWidth) currentX += setWidth;

        track.style.transform = `translateX(${currentX}px)`;
      });

      document.addEventListener('mouseup', () => {
        if (!isDragging) return;
        isDragging = false;
        container.classList.remove('dragging');

        // Normalizar posición para reanudar animación
        currentX = currentX % setWidth; // Ajusta al set más cercano
        if (currentX < -setWidth / 2) currentX += setWidth; // Evita negativos grandes
        track.style.transition = 'transform 0.3s ease-out';
        track.style.transform = `translateX(${currentX}px)`;

        // Reanudar animación CSS
        setTimeout(() => {
          if (!isDragging) {
            // Ajustar posición para que coincida con el keyframe
            let offsetPercentage = (currentX / setWidth) * 33.333; // Convertir a % del set
            track.style.transform = `translateX(${offsetPercentage}%)`;
            track.classList.add('scrolling');
            isAnimating = true;
          }
        }, 300);
      });

      // PASO 5: Touch events
      container.addEventListener('touchstart', (e) => {
        if (isDragging) return;
        isDragging = true;
        container.classList.add('dragging');
        track.classList.remove('scrolling');
        isAnimating = false;
        track.style.transition = 'none';

        initialX = currentX;
        startX = e.touches[0].pageX - container.offsetLeft;
      }, {
        passive: false
      });

      container.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.touches[0].pageX - container.offsetLeft;
        const walk = (x - startX) * 1.2;
        currentX = initialX + walk;

        // Loop infinito
        while (currentX > 0) currentX -= setWidth;
        while (currentX < -setWidth) currentX += setWidth;

        track.style.transform = `translateX(${currentX}px)`;
      }, {
        passive: false
      });

      container.addEventListener('touchend', () => {
        if (!isDragging) return;
        isDragging = false;
        container.classList.remove('dragging');

        // Normalizar posición
        currentX = currentX % setWidth;
        if (currentX < -setWidth / 2) currentX += setWidth;
        track.style.transition = 'transform 0.3s ease-out';
        track.style.transform = `translateX(${currentX}px)`;

        // Reanudar animación CSS
        setTimeout(() => {
          if (!isDragging) {
            let offsetPercentage = (currentX / setWidth) * 33.333;
            track.style.transform = `translateX(${offsetPercentage}%)`;
            track.classList.add('scrolling');
            isAnimating = true;
          }
        }, 300);
      });

      // Prevenir defaults
      container.addEventListener('dragstart', e => e.preventDefault());
      container.addEventListener('selectstart', e => e.preventDefault());

      // Prevenir clicks si drag
      let clickThreshold = 5;
      container.addEventListener('click', (e) => {
        if (Math.abs(currentX - initialX) > clickThreshold) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);
    }

    // --- Inicializar (sin cambios) ---
    document.addEventListener('DOMContentLoaded', function() {
      setupDragCarousel('.products-carousel-container');
    });
  </script>
</body>

</html>