<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cantidadEnCarrito = 0;
if (isset($_SESSION["carrito"])) {
    foreach ($_SESSION["carrito"] as $item) {
        $cantidadEnCarrito += $item["cantidad"];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<?php include_once __DIR__ .
    '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/home.css') ?>">

<body>
    <!-- Barra superior -->
    <div class="header-container">
        <?php include_once __DIR__ .
            '/../admin/includes/header.php'; ?>
    </div>

    <?php if (!empty($banners)): ?>
        <div class="hero-banner">
            <div class="hero-track" id="heroTrack">
                <?php foreach ($banners as $ban): ?>
                    <div class="hero-slide">
                        <img src="<?= url('uploads/banners/' . $ban['nombre_imagen']) ?>" alt="banner">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <div class="content-wrapper">
            <!-- Título -->
            <div class="welcome-section">
                <h1 class="main-title">Bienvenido a Bytebox</h1>
                <p class="main-subtitle">Explora nuestros productos y encuentra lo que buscas</p>
            </div>

            <div class="main-content-area">
                <!-- 🔍 Filtros de búsqueda (más pequeño y a la izquierda) -->
                <div class="filters-sidebar">
                    <form id="filtroForm" method="GET" action="<?= url('home/index') ?>" class="vertical-filters">
                        <div class="filter-group-title">
                            <h3>🔍 Filtros</h3>
                        </div>

                        <div class="filter-group">
                            <label for="min_price">Precio Mín</label>
                            <input type="number" id="min_price" name="min_price" value="<?= $_GET['min_price'] ?? '' ?>" step="1" min="0" placeholder="Mín">
                        </div>

                        <div class="filter-group">
                            <label for="max_price">Precio Máx</label>
                            <input type="number" id="max_price" name="max_price" value="<?= $_GET['max_price'] ?? '' ?>" step="1" min="0" placeholder="Máx">
                        </div>

                        <div class="filter-group">
                            <label for="categoria">Categoría</label>
                            <select name="categoria" id="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categoriasDisponibles as $categoria): ?>
                                    <option value="<?= $categoria['id'] ?>" <?= ($_GET['categoria'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="orden">Ordenar</label>
                            <select name="orden" id="orden">
                                <option value="">--</option>
                                <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio ↑</option>
                                <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio ↓</option>
                                <option value="nombre_asc" <?= ($_GET['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre A-Z</option>
                                <option value="nombre_desc" <?= ($_GET['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre Z-A</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="filter-button">Filtrar</button>
                            <a href="<?= url('home/index') ?>" class="clear-button">Eliminar Filtros</a>
                        </div>
                    </form>
                </div>

                <!-- Productos -->
                <div class="products-content">
                    <div class="products-grid">
                        <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $producto): ?>
                                <?php
                                // ---------- LÓGICA DE DESCUENTO POR PRODUCTO ----------
                                $precioFinal = isset($producto['precio']) ? (float)$producto['precio'] : 0.0;
                                $precioTachado = isset($producto['precio_tachado']) && $producto['precio_tachado'] !== ''
                                    ? (float)$producto['precio_tachado'] : null;

                                $tachadoVisible = !empty($producto['precio_tachado_visible']); // 1/0
                                $pctVisible     = !empty($producto['porcentaje_visible']);     // 1/0

                                $descuentoPct = isset($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] !== ''
                                    ? (float)$producto['porcentaje_descuento'] : 0.0;

                                if (($descuentoPct <= 0 || $descuentoPct > 100) && $precioTachado !== null && $precioTachado > 0 && $precioFinal < $precioTachado) {
                                    $descuentoPct = round((($precioTachado - $precioFinal) / $precioTachado) * 100);
                                }

                                $showTachado = ($precioTachado !== null) && ($precioTachado > $precioFinal) && $tachadoVisible;
                                $showPct     = $pctVisible && $showTachado && ($descuentoPct > 0);

                                // Imagen principal (primera) o default
                                $imgSrc = url('uploads/default-product.png');
                                if (!empty($producto['imagenes']) && !empty($producto['imagenes'][0]['nombre_imagen'])) {
                                    $imgSrc = url('uploads/' . $producto['imagenes'][0]['nombre_imagen']);
                                } elseif (!empty($producto['imagen'])) {
                                    // compatibilidad por si vienes de una consulta que trae 'imagen'
                                    $imgSrc = url('uploads/' . $producto['imagen']);
                                }
                                ?>
                                <div class="product-card">
                                    <?php if ($showPct): ?>
                                        <div class="badge-porcentaje">-<?= number_format($descuentoPct) ?>%</div>
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
                                            <button type="submit" class="add-button">🛒 Agregar</button>
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

                <?php if ($totalPaginas > 1): ?>
                    <div class="pagination">
                        <?php if ($paginaActual > 1): ?>
                            <a href="?pagina=<?= $paginaActual - 1 ?>" class="page-link">Anterior</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a href="?pagina=<?= $i ?>" class="page-link <?= $i == $paginaActual ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a href="?pagina=<?= $paginaActual + 1 ?>" class="page-link">Siguiente</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ .
        '/../admin/includes/footer.php'; ?>
    <?php
    $mostrarBannerCookies = true;

    // Si cliente está logueado, revisa BD
    if (isset($_SESSION['cliente_id'])) {
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT cookies_consent FROM clientes WHERE id = ?");
        $stmt->execute([$_SESSION['cliente_id']]);
        $consent = $stmt->fetchColumn();

        if ($consent !== null) {
            $mostrarBannerCookies = false;
        }
    }
    // Si no está logueado, revisa cookie local
    elseif (isset($_COOKIE['cookies_consent'])) {
        $mostrarBannerCookies = false;
    }
    ?>

    <?php if ($mostrarBannerCookies): ?>
        <div id="cookie-banner" class="cookie-banner">
            <p>Usamos cookies para mejorar tu experiencia. ¿Aceptas su uso?</p>
            <button id="accept-cookies">Aceptar</button>
            <button id="reject-cookies">Rechazar</button>
        </div>
        <script>
            document.getElementById('accept-cookies').addEventListener('click', () => enviarConsent(1));
            document.getElementById('reject-cookies').addEventListener('click', () => enviarConsent(0));

            function enviarConsent(valor) {
                fetch("<?= url('clientes/guardarConsentimientoCookies') ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "consent=" + valor
                }).then(() => {
                    document.getElementById('cookie-banner').style.display = 'none';
                });
            }
        </script>
    <?php endif; ?>
    <script>
        const filtroForm = document.getElementById('filtroForm');
        filtroForm.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', () => {
                filtroForm.submit();
            });
        });
    </script>
    <script>
        (function() {
            const track = document.getElementById('heroTrack');
            if (!track) return;

            const slides = Array.from(track.children);
            if (slides.length <= 1) return;

            // Clonamos las diapositivas para crear el efecto de loop infinito
            slides.forEach(slide => {
                const clone = slide.cloneNode(true);
                track.appendChild(clone);
            });

            let i = 0;
            const totalSlides = track.children.length;

            function avanzar() {
                i++;
                track.style.transition = "transform 0.6s ease";
                track.style.transform = `translateX(-${i * 100}%)`;

                // Si llegamos al final del clon, reiniciamos sin animación
                if (i === totalSlides - slides.length) {
                    setTimeout(() => {
                        track.style.transition = "none";
                        i = 0;
                        track.style.transform = `translateX(0)`;
                    }, 600); // mismo tiempo que el transition
                }
            }

            setInterval(avanzar, 4500);
        })();
    </script>


</body>

</html>