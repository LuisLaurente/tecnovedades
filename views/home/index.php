<?php
// views/home/index.php (versión corregida)

$metaTitle = "Bienvenido a Tienda Tecnovedades - Tecnología y Novedades";
$metaDescription = "Descubre lo último en tecnología, novedades y accesorios al mejor precio.";

if (session_status() === PHP_SESSION_NONE) session_start();

// carrito count
$cantidadEnCarrito = 0;
if (isset($_SESSION["carrito"])) {
    foreach ($_SESSION["carrito"] as $item) {
        $cantidadEnCarrito += $item["cantidad"];
    }
}

// currentQuery (sin pagina/ajax) para la paginación
$currentQuery = $_GET ?? [];
unset($currentQuery['pagina'], $currentQuery['ajax']);

// Aseguramos que las variables que usan los parciales existan (fallbacks seguros)
if (!isset($paginaActual)) {
    $paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0
        ? (int) $_GET['pagina'] : 1;
}
if (!isset($totalPaginas)) {
    // Si el controlador no definió totalPaginas, intentamos calcular con variables auxiliares
    if (isset($totalFiltrados) && isset($productosPorPagina) && $productosPorPagina > 0) {
        $totalPaginas = (int) max(1, ceil($totalFiltrados / $productosPorPagina));
    } else {
        $totalPaginas = 1;
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

    <!-- Banners -->
    <?php if (!empty($banners)): ?>
        <div class="hero-banner">
            <div class="hero-track" id="heroTrack">
                <?php foreach ($banners as $ban): ?>
                    <div class="hero-slide">
                        <img src="<?= url('uploads/banners/' . htmlspecialchars($ban['nombre_imagen'])) ?>" alt="banner">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <div class="content-wrapper">
            <!-- Categorías -->
            <?php if (!empty($categoriasDisponibles)): ?>
                <section class="categories-strip">
                    <?php foreach ($categoriasDisponibles as $cat):
                        $catId = (int)($cat['id'] ?? 0);
                        $catName = htmlspecialchars($cat['nombre'] ?? 'Categoría');
                        $catImg = !empty($cat['imagen']) ? url('uploads/categorias/' . $cat['imagen']) : url('uploads/default-category.png');
                    ?>
                        <a class="category-pill" href="<?= url('home/index?categoria=' . $catId) ?>" title="<?= $catName ?>">
                            <div class="category-image" style="background-image:url('<?= $catImg ?>');"></div>
                            <div class="category-name"><?= $catName ?></div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <div class="main-content-area">
                <!-- filtros -->
                <aside class="filters-sidebar">
                    <form id="filtroForm" method="GET" action="<?= url('home/index') ?>" class="vertical-filters">
                        <div class="filter-group-title">
                            <h3>🔍 Filtros</h3>
                        </div>

                        <div class="filter-group">
                            <label for="min_price">Precio Mín</label>
                            <input type="number" id="min_price" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" step="1" min="0" placeholder="Mín">
                        </div>

                        <div class="filter-group">
                            <label for="max_price">Precio Máx</label>
                            <input type="number" id="max_price" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" step="1" min="0" placeholder="Máx">
                        </div>

                        <div class="filter-group">
                            <label for="categoria">Categoría</label>
                            <select name="categoria" id="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categoriasDisponibles as $categoria): ?>
                                    <option value="<?= (int)$categoria['id'] ?>" <?= (($_GET['categoria'] ?? '') == $categoria['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="orden">Ordenar</label>
                            <select name="orden" id="orden">
                                <option value="">--</option>
                                <option value="precio_asc" <?= (($_GET['orden'] ?? '') === 'precio_asc')  ? 'selected' : '' ?>>Precio ↑</option>
                                <option value="precio_desc" <?= (($_GET['orden'] ?? '') === 'precio_desc') ? 'selected' : '' ?>>Precio ↓</option>
                                <option value="nombre_asc" <?= (($_GET['orden'] ?? '') === 'nombre_asc')  ? 'selected' : '' ?>>Nombre A-Z</option>
                                <option value="nombre_desc" <?= (($_GET['orden'] ?? '') === 'nombre_desc') ? 'selected' : '' ?>>Nombre Z-A</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="filter-button">Filtrar</button>
                            <a href="<?= url('home/index') ?>" class="clear-button">Limpiar Filtros</a>
                        </div>
                    </form>
                </aside>

                <!-- Productos area -->
                <section class="products-content">
                    <div class="products-header" style="display:flex; align-items:center; justify-content:space-between;">
                        <!-- Paginador superior (izquierda) -->
                        <div class="pagination-top">
                            <?php
                            // la parcial _pagination.php espera $paginaActual, $totalPaginas, $currentQuery
                            // Los definimos (ya arriba) y luego incluimos la parcial
                            include __DIR__ . '/_pagination.php';
                            ?>
                        </div>

                        <div class="products-controls">
                            <!-- Ej: "Mostrando X de Y" -->
                            <?php if (isset($totalFiltrados) && isset($productosPorPagina)): ?>
                                <?php
                                $start = (($paginaActual - 1) * $productosPorPagina) + 1;
                                $end = min($totalFiltrados, $paginaActual * $productosPorPagina);
                                ?>
                                <div class="results-info">Mostrando <?= $start ?>-<?= $end ?> de <?= $totalFiltrados ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Productos (parcial) -->
                    <?php include __DIR__ . '/_products_grid.php'; ?>

                    <!-- Paginador inferior centrado -->
                    <div class="pagination-bottom" style="margin-top:1.25rem; display:flex; justify-content:center;">
                        <?php include __DIR__ . '/_pagination.php'; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

    <!-- Cookie banner (mantengo tu lógica previa si la tienes) -->
    <?php
    $mostrarBannerCookies = true;
    if (isset($_COOKIE['cookies_consent'])) {
        $mostrarBannerCookies = false;
    }
    if ($mostrarBannerCookies): ?>
        <div id="cookie-banner" class="cookie-banner">
            <p>Usamos cookies para mejorar tu experiencia. ¿Aceptas su uso?</p>
            <button id="accept-cookies">Aceptar</button>
            <button id="reject-cookies">Rechazar</button>
        </div>
        <script>
            document.getElementById('accept-cookies').addEventListener('click', () => {
                document.cookie = 'cookies_consent=1; expires=' + new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                document.getElementById('cookie-banner').style.display = 'none';
            });
            document.getElementById('reject-cookies').addEventListener('click', () => {
                document.cookie = 'cookies_consent=0; expires=' + new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                document.getElementById('cookie-banner').style.display = 'none';
            });
        </script>
    <?php endif; ?>

    <!-- AJAX Pagination & Filters (mejorado: soporta distintas formas de respuesta JSON) -->
    <script>
        (function() {
            const baseUrl = '<?= url('home/index') ?>';
            const filtroForm = document.getElementById('filtroForm');

            // helper: construye href limpio (sin ajax param)
            function cleanHref(href) {
                try {
                    const u = new URL(href, location.origin);
                    u.searchParams.delete('ajax');
                    return u.toString();
                } catch (e) {
                    return href;
                }
            }

            async function fetchAndRender(href, push = true) {
                try {
                    // construimos la URL con ajax=1
                    const urlObj = new URL(href, location.origin);
                    urlObj.searchParams.set('ajax', '1');
                    const ajaxUrl = urlObj.toString();

                    const res = await fetch(ajaxUrl, {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Petición fallida: ' + res.status);
                    const data = await res.json();

                    // productos HTML (compatible con varios nombres de campo)
                    const productsHtml = data.products_html || data.productsHtml || data.products || '';
                    // paginación: acepta pagination_html o pagination_top_html & pagination_bottom_html
                    const paginationTop = data.pagination_top_html || data.pagination_html || data.paginationTopHtml || '';
                    const paginationBottom = data.pagination_bottom_html || data.pagination_html || data.paginationBottomHtml || '';

                    // Reemplazar DOM
                    const productsWrapper = document.getElementById('productsWrapper');
                    if (productsWrapper && productsHtml) {
                        productsWrapper.innerHTML = productsHtml;
                    } else if (!productsHtml) {
                        // fallback: recarga completa si no hay HTML
                        window.location.href = cleanHref(href);
                        return;
                    }

                    // reemplazar paginadores (si vienen)
                    const top = document.querySelector('.pagination-top');
                    const bottom = document.querySelector('.pagination-bottom');
                    if (top) top.innerHTML = paginationTop || data.pagination_html || top.innerHTML;
                    if (bottom) bottom.innerHTML = paginationBottom || data.pagination_html || bottom.innerHTML;

                    // actualizar URL sin ajax param
                    if (push) {
                        const pushUrl = cleanHref(href);
                        history.pushState(null, '', pushUrl);
                    }

                    // scroll suave a productos
                    const productsTop = document.querySelector('.products-content');
                    if (productsTop) window.scrollTo({
                        top: productsTop.getBoundingClientRect().top + window.scrollY - 80,
                        behavior: 'smooth'
                    });

                } catch (err) {
                    console.error('AJAX paginación error:', err);
                    // fallback a navegación normal
                    window.location.href = href;
                }
            }

            // Delegación: interceptar clicks en links .page-link
            document.addEventListener('click', function(e) {
                const a = e.target.closest('.page-link');
                if (!a) return;
                const href = a.getAttribute('href');
                if (!href) return;
                // solo interceptamos enlaces internos de paginación
                // evita interceptar enlaces externos
                const url = new URL(href, location.origin);
                if (url.origin !== location.origin) return;
                e.preventDefault();
                fetchAndRender(url.toString(), true);
            });

            // Interceptar cambios de filtros (submit y change)
            if (filtroForm) {
                filtroForm.addEventListener('submit', function(ev) {
                    ev.preventDefault();
                    const params = new URLSearchParams(new FormData(filtroForm));
                    params.set('pagina', '1');
                    const href = baseUrl + '?' + params.toString();
                    fetchAndRender(href, true);
                });

                filtroForm.querySelectorAll('input, select').forEach(el => {
                    el.addEventListener('change', () => {
                        const params = new URLSearchParams(new FormData(filtroForm));
                        params.set('pagina', '1');
                        const href = baseUrl + '?' + params.toString();
                        fetchAndRender(href, true);
                    });
                });
            }

            // Manejo de back/forward
            window.addEventListener('popstate', function() {
                // cargamos la URL actual por AJAX (no push)
                fetchAndRender(location.href, false);
            });

            // Limpiar ajax=1 del query string al inicio si existe
            if (location.search.includes('ajax=1')) {
                const url = new URL(location.href);
                url.searchParams.delete('ajax');
                history.replaceState(null, '', url.toString());
            }
        })();
    </script>

    <!-- Slider (tu script) -->
    <script>
        (function() {
            const track = document.getElementById('heroTrack');
            if (!track) return;
            const slides = Array.from(track.children);
            if (slides.length <= 1) return;
            slides.forEach(slide => track.appendChild(slide.cloneNode(true)));
            let i = 0;
            const totalSlides = track.children.length;

            function avanzar() {
                i++;
                track.style.transition = "transform 0.6s ease";
                track.style.transform = `translateX(-${i * 100}%)`;
                if (i === totalSlides - slides.length) {
                    setTimeout(() => {
                        track.style.transition = "none";
                        i = 0;
                        track.style.transform = `translateX(0)`;
                    }, 600);
                }
            }
            setInterval(avanzar, 4500);
        })();
    </script>
</body>

</html>