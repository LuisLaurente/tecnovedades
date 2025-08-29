<?php
// views/home/busqueda.php (VISTA FINAL PARA TIENDA Y BÚSQUEDA)
// -----------------------------------------------------------------
// Esta vista muestra la grilla completa de productos con filtros y paginación.
// Espera recibir del controlador las siguientes variables:
// - $productos: El array de productos a mostrar.
// - $categoriasDisponibles: Para el select de categorías en los filtros.
// - $todasEtiquetas: Para los filtros por etiquetas (si los implementas).
// - $paginaActual, $totalPaginas, $currentQuery: Para la paginación.
// - (Opcional) $termino: Si la vista se usa para resultados de una búsqueda por texto.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
    <!-- Los estilos son los mismos que usaba tu home original, asegurando consistencia visual -->
    <link rel="stylesheet" href="<?= url('css/home.css') ?>">
    <link rel="stylesheet" href="<?= url('css/cards.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="main-container">
        <div class="content-wrapper">
            
            <!-- ================================================================== -->
            <!-- TÍTULO DE LA PÁGINA                                               -->
            <!-- ================================================================== -->
            <!-- Puede mostrar un título genérico o el término de búsqueda.      -->
            <div class="welcome-section" style="text-align: center; margin-bottom: 2rem;">
                <?php if (!empty($termino )): ?>
                    <h1 class="main-title">Resultados para: "<?= htmlspecialchars($termino, ENT_QUOTES, 'UTF-8') ?>"</h1>
                <?php elseif (!empty($categoriaActual)): ?>
                     <h1 class="main-title">Categoría: <?= htmlspecialchars($categoriaActual['nombre'], ENT_QUOTES, 'UTF-8') ?></h1>
                <?php else: ?>
                    <h1 class="main-title">Nuestra Tienda</h1>
                    <p class="main-subtitle">Explora todo nuestro catálogo de productos</p>
                <?php endif; ?>
            </div>

            <!-- ================================================================== -->
            <!-- ÁREA DE CONTENIDO PRINCIPAL (FILTROS + PRODUCTOS)                 -->
            <!-- ================================================================== -->
            <div class="main-content-area">
                
                <!-- === BARRA LATERAL DE FILTROS === -->
                <aside class="filters-sidebar">
                    <form id="filtroForm" method="GET" action="<?= url('home/busqueda') ?>" class="vertical-filters">
                        <div class="filter-group-title">
                            <h3><i class="fa-solid fa-filter"></i> Filtros</h3>
                        </div>

                        <!-- Filtro por Precio -->
                        <div class="filter-group">
                            <label for="min_price">Precio Mínimo</label>
                            <input type="number" id="min_price" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" placeholder="S/ 0">
                        </div>
                        <div class="filter-group">
                            <label for="max_price">Precio Máximo</label>
                            <input type="number" id="max_price" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" placeholder="S/ 5000">
                        </div>

                        <!-- Filtro por Categoría -->
                        <div class="filter-group">
                            <label for="categoria">Categoría</label>
                            <select name="categoria" id="categoria">
                                <option value="">Todas</option>
                                <?php if (!empty($categoriasDisponibles)): ?>
                                    <?php foreach ($categoriasDisponibles as $categoria): ?>
                                        <option value="<?= (int)$categoria['id'] ?>" <?= (($_GET['categoria'] ?? '') == $categoria['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro por Orden -->
                        <div class="filter-group">
                            <label for="orden">Ordenar por</label>
                            <select name="orden" id="orden">
                                <option value="">Relevancia</option>
                                <option value="precio_asc" <?= (($_GET['orden'] ?? '') === 'precio_asc')  ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                                <option value="precio_desc" <?= (($_GET['orden'] ?? '') === 'precio_desc') ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                                <option value="nombre_asc" <?= (($_GET['orden'] ?? '') === 'nombre_asc')  ? 'selected' : '' ?>>Nombre: A-Z</option>
                                <option value="nombre_desc" <?= (($_GET['orden'] ?? '') === 'nombre_desc') ? 'selected' : '' ?>>Nombre: Z-A</option>
                            </select>
                        </div>

                        <!-- Acciones del formulario -->
                        <div class="filter-actions">
                            <button type="submit" class="filter-button">Aplicar Filtros</button>
                            <a href="<?= url('home/busqueda') ?>" class="clear-button">Limpiar</a>
                        </div>
                    </form>
                </aside>

                <!-- === CONTENIDO DE PRODUCTOS === -->
                <section class="products-content">
                    <!-- Paginación Superior -->
                    <div class="products-header">
                        <?php if (isset($totalPaginas)) include __DIR__ . '/_pagination.php'; ?>
                    </div>

                    <!-- Grilla de Productos (reutilizando la parcial) -->
                    <?php include __DIR__ . '/_products_grid.php'; ?>

                    <!-- Paginación Inferior -->
                    <div class="pagination-bottom">
                        <?php if (isset($totalPaginas)) include __DIR__ . '/_pagination.php'; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

    <!-- ================================================================== -->
    <!-- SCRIPTS PARA FILTROS Y PAGINACIÓN AJAX                           -->
    <!-- ================================================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filtroForm = document.getElementById('filtroForm');
            const productsContent = document.querySelector('.products-content');

            // --- Función para manejar la carga de contenido con AJAX ---
            async function fetchContent(url, pushState = true) {
                // Añadir parámetro 'ajax=1' para que el controlador devuelva JSON
                const ajaxUrl = new URL(url, window.location.origin);
                ajaxUrl.searchParams.set('ajax', '1');

                try {
                    const response = await fetch(ajaxUrl.toString());
                    if (!response.ok) throw new Error('Network response was not ok.');
                    const data = await response.json();

                    // Actualizar la grilla de productos y la paginación
                    if (data.products_html) {
                        productsContent.innerHTML = data.products_html;
                    }
                    if (data.pagination_html) {
                        // Reemplazar ambos paginadores
                        const topPagination = productsContent.querySelector('.products-header');
                        const bottomPagination = productsContent.querySelector('.pagination-bottom');
                        if(topPagination) topPagination.innerHTML = data.pagination_html;
                        if(bottomPagination) bottomPagination.innerHTML = data.pagination_html;
                    }

                    // Actualizar la URL del navegador sin recargar la página
                    if (pushState) {
                        history.pushState({}, '', url);
                    }
                } catch (error) {
                    console.error('Error al cargar los filtros:', error);
                    window.location.href = url; // Fallback: recarga normal si AJAX falla
                }
            }

            // --- Manejar el envío del formulario de filtros ---
            if (filtroForm) {
                filtroForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const params = new URLSearchParams(formData);
                    const url = this.action + '?' + params.toString();
                    fetchContent(url);
                });
            }

            // --- Manejar los clics en los enlaces de paginación ---
            document.addEventListener('click', function (e) {
                // Usamos delegación de eventos para capturar clics en enlaces de paginación
                const pageLink = e.target.closest('.page-link');
                if (pageLink && pageLink.tagName === 'A') {
                    e.preventDefault();
                    const url = pageLink.getAttribute('href');
                    if (url) {
                        fetchContent(url);
                    }
                }
            });

            // --- Manejar los botones de atrás/adelante del navegador ---
            window.addEventListener('popstate', function () {
                // Carga el contenido de la URL actual cuando el usuario navega por el historial
                fetchContent(window.location.href, false);
            });
        });
    </script>
</body>
</html>
