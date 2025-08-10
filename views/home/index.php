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

<body>
    <!-- Barra superior -->
    <div class="header-container">
        <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    </div>

    <div class="main-container">
        <div class="content-wrapper">
            <!-- Título -->
            <div class="welcome-section">
                <h1 class="main-title">Bienvenido a Bytebox</h1>
                <p class="main-subtitle">Explora nuestros productos y encuentra lo que buscas</p>
            </div>


            <!-- 🔍 Filtros de búsqueda -->
            <div class="filters-section">
                <form id="filtroForm" method="GET" action="<?= url('home/index') ?>" class="horizontal-filters">
                    <div class="filter-group">
                        <h3>🔍 Filtros</h3>
                        <?php if (isset($estadisticasPrecios)): ?>
                            <div class="stats">
                                <span>S/ <?= $estadisticasPrecios['precio_minimo'] ?>-<?= $estadisticasPrecios['precio_maximo'] ?></span>
                            </div>
                            <div class="stats">
                                <span><?= $estadisticasPrecios['total_productos'] ?> productos</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="filter-group">
                        <label>Precio Mín</label>
                        <input type="number" name="min_price" value="<?= $_GET['min_price'] ?? '' ?>" step="1" min="0" placeholder="Mín">
                    </div>

                    <div class="filter-group">
                        <label>Precio Máx</label>
                        <input type="number" name="max_price" value="<?= $_GET['max_price'] ?? '' ?>" step="1" min="0" placeholder="Máx">
                    </div>

                    <div class="filter-group">
                        <label>Categoría</label>
                        <select name="categoria">
                            <option value="">Todas</option>
                            <?php foreach ($categoriasDisponibles as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($_GET['categoria'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>
                            <input type="checkbox" name="disponibles" value="1" <?= isset($_GET['disponibles']) ? 'checked' : '' ?>>
                            Disponibles
                        </label>
                    </div>

                    <div class="filter-group">
                        <label>Ordenar</label>
                        <select name="orden">
                            <option value="">--</option>
                            <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio ↑</option>
                            <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio ↓</option>
                            <option value="nombre_asc" <?= ($_GET['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre A-Z</option>
                            <option value="nombre_desc" <?= ($_GET['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre Z-A</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="filter-button">Filtrar</button>
                        <a href="<?= url('home/index') ?>" class="clear-button">Eliminar Filtros</a>
                    </div>
                </form>

                <!-- Etiquetas (se muestra solo cuando es relevante) -->
                <div class="tags-container">
                    <fieldset>
                        <legend>Etiquetas:</legend>
                        <?php foreach ($todasEtiquetas as $etiqueta): ?>
                            <label class="tag-label">
                                <input type="checkbox" name="etiquetas[]" value="<?= $etiqueta['id'] ?>"
                                    <?= in_array($etiqueta['id'], $_GET['etiquetas'] ?? []) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($etiqueta['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                </div>
            </div>

            <!-- Productos -->
            <div class="products-grid">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="product-card">
                            <a href="<?= url('producto/ver/' . $producto['id']) ?>" class="product-link">
                                <div class="product-image-container">
                                    <img src="<?= url('uploads/' . $producto['imagen']) ?>"
                                        alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                </div>

                                <div class="product-info">
                                    <h3 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                    <p class="product-description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                    <div class="product-price">
                                        S/ <?= number_format($producto['precio'], 2) ?>
                                    </div>
                                </div>
                            </a>

                            <form method="POST" action="<?= url('carrito/agregar') ?>" class="add-to-cart-form" onClick="event.stopPropagation();">
                                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                <div class="quantity-section">
                                    <input type="number" name="cantidad" value="1" min="1" class="quantity-input">
                                    <button type="submit" class="add-button">
                                        🛒 Agregar
                                    </button>
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
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
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

        // Detectar cambios en inputs, selects y checkboxes
        filtroForm.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', () => {
                filtroForm.submit(); // Enviar el formulario automáticamente
            });
        });
    </script>
</body>

</html>