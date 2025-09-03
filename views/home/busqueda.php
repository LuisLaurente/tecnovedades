<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Calcular información de productos
$totalProductos = isset($productos) ? count($productos) : 0;
$productosPorPagina = 15; // Ajustar según tu configuración
$totalEncontrados = $totalProductos; // Esto debería venir del controlador
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
    <!-- Meta tags para SEO -->
    <meta name="description" content="<?= !empty($termino) ? 'Resultados de búsqueda para: ' . htmlspecialchars($termino) : 'Explora nuestro catálogo completo de productos electrónicos' ?>">
    <meta name="keywords" content="electrónicos, tecnología, productos, tienda, búsqueda, catálogo">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos de noUiSlider -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" />

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= url('css/busqueda.css') ?>">
    <link rel="stylesheet" href="<?= url('css/cards.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Preload para mejorar rendimiento -->
    <link rel="preload" href="<?= url('css/busqueda.css') ?>" as="style">
</head>

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="main-container">
        <div class="content-wrapper">
            <!-- Debug temporal - eliminar después -->
            <?php if (false): // Cambia a true para activar debug ?>
            <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ddd;">
                <h3>Debug Variables:</h3>
                <p><strong>termino:</strong> <?= isset($termino) ? htmlspecialchars($termino) : 'NO DEFINIDO' ?></p>
                <p><strong>productos:</strong> <?= isset($productos) ? count($productos) . " productos encontrados" : 'NO DEFINIDO' ?></p>
                <p><strong>totalEncontrados:</strong> <?= isset($totalEncontrados) ? $totalEncontrados : 'NO DEFINIDO' ?></p>
                <?php if (isset($productos) && count($productos) > 0): ?>
                    <p><strong>Primer producto:</strong> <?= htmlspecialchars($productos[0]['nombre'] ?? 'Sin nombre') ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

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

            <!-- ================================================================== -->
            <!-- BARRA DE FILTROS Y HERRAMIENTAS                                    -->
            <!-- ================================================================== -->
            <div class="toolbar">
                <form id="filterForm" class="filter-form-container" method="GET" action="<?= url('home/busqueda') ?>">
                    <h2 class="filters-title">Filtros</h2>
                    <div class="filters-wrapper">
                        <!-- Mantener término de búsqueda si existe -->
                        <?php if (!empty($termino)): ?>
                            <input type="hidden" name="termino" value="<?= htmlspecialchars($termino) ?>">
                        <?php endif; ?>

                        <!-- Filtro por Categoría -->
                        <div class="filter-group-inline">
                            <label class="filter-label">CATEGORÍAS</label>
                            <select name="categoria" class="filter-select">
                                <option value="">Todas las categorías</option>
                                <?php if (!empty($categoriasDisponibles)): ?>
                                    <?php foreach ($categoriasDisponibles as $categoria): ?>
                                        <option value="<?= (int)$categoria['id'] ?>"
                                            <?= (($_GET['categoria'] ?? '') == $categoria['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro por Precio -->
                        <div class="filter-group-inline price-filter">
                            <label class="filter-label">PRECIOS</label>
                            <div class="price-inputs-inline">
                                <div class="price-inputs-group">
                                    <input type="number" class="price-input-inline" name="min_price" value="<?= htmlspecialchars($_GET['min_price'] ?? '30') ?>" placeholder="Mín">
                                    <input type="number" class="price-input-inline" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '1000') ?>" placeholder="Máx">
                                </div>
                                <div id="price-slider" class="price-slider-container"></div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="filter-actions-inline">
                            <button type="submit" class="apply-filters-btn">
                                <i class="fas fa-search"></i> BUSCAR
                            </button>
                            <a href="<?= url('home/busqueda') ?>" class="clear-filters-btn">
                                Limpiar filtros
                            </a>
                        </div>

                        <!-- Ordenamiento -->
                        <div class="sort-section">
                            <label class="sort-label">Ordenar:</label>
                            <select class="sort-select" id="sortSelect" name="orden">
                                <option value="">Ordenar por:</option>
                                <option value="nombre_asc" <?= (($_GET['orden'] ?? '') === 'nombre_asc') ? 'selected' : '' ?>>Ordenar de A - Z</option>
                                <option value="nombre_desc" <?= (($_GET['orden'] ?? '') === 'nombre_desc') ? 'selected' : '' ?>>Ordenar de Z - A</option>
                                <option value="precio_asc" <?= (($_GET['orden'] ?? '') === 'precio_asc') ? 'selected' : '' ?>>Menor a mayor precio</option>
                                <option value="precio_desc" <?= (($_GET['orden'] ?? '') === 'precio_desc') ? 'selected' : '' ?>>Mayor a menor precio</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ================================================================== -->
            <!-- PAGINACIÓN (SUPERIOR)                                              -->
            <!-- ================================================================== -->
            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                <?php include __DIR__ . '/_pagination.php'; ?>
            <?php endif; ?>

            <!-- ================================================================== -->
            <!-- GRID DE PRODUCTOS                                                 -->
            <!-- ================================================================== -->
            <div id="products-container">
                <?php include __DIR__ . '/_products_grid.php'; ?>
            </div>

            <!-- ================================================================== -->
            <!-- PAGINACIÓN (INFERIOR)                                              -->
            <!-- ================================================================== -->
            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                <?php include __DIR__ . '/_pagination.php'; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>


    <!-- Librería noUiSlider (Añadir esta línea) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            //================================================
            // INICIALIZACIÓN DEL SLIDER DE PRECIOS (noUiSlider)
            //================================================
            const priceSlider = document.getElementById('price-slider');

            if (priceSlider) {
                const minPriceInput = document.querySelector('input[name="min_price"]');
                const maxPriceInput = document.querySelector('input[name="max_price"]');

                // Define un rango máximo realista para los productos. Ajústalo si es necesario.
                const maxRange = 5000;

                // Valores iniciales: toma los de los inputs o usa valores por defecto.
                const initialMin = parseInt(minPriceInput.value, 10) || 0;
                const initialMax = parseInt(maxPriceInput.value, 10) || maxRange;

                noUiSlider.create(priceSlider, {
                    start: [initialMin, initialMax], // Posición inicial de los círculos
                    connect: true, // Rellena el espacio entre los círculos
                    step: 10, // El valor se mueve en saltos de 10
                    range: {
                        'min': 0,
                        'max': maxRange
                    },
                    format: { // Asegura que los valores sean números enteros
                        to: function(value) {
                            return Math.round(value);
                        },
                        from: function(value) {
                            return Number(value);
                        }
                    }
                });

                // Evento: Cuando mueves el slider, actualiza los campos de texto (inputs)
                priceSlider.noUiSlider.on('update', function(values, handle) {
                    if (handle === 0) { // Círculo izquierdo (precio mínimo)
                        minPriceInput.value = values[0];
                    } else { // Círculo derecho (precio máximo)
                        maxPriceInput.value = values[1];
                    }
                });

                // Evento: Cuando cambias el valor en el input, mueve el slider
                minPriceInput.addEventListener('change', function() {
                    priceSlider.noUiSlider.set([this.value, null]);
                });

                maxPriceInput.addEventListener('change', function() {
                    priceSlider.noUiSlider.set([null, this.value]);
                });
            }

            //================================================
            // MANEJO DEL FORMULARIO DE FILTROS Y ORDENAMIENTO
            //================================================
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                // Envía el formulario automáticamente cuando se cambia el ordenamiento
                document.getElementById('sortSelect').addEventListener('change', () => {
                    filterForm.submit();
                });
            }

            //================================================
            // MANEJO DE BOTONES DE FAVORITOS
            //================================================
            document.addEventListener('click', function(e) {
                // Busca si el clic fue en un botón de favoritos
                const favoriteButton = e.target.closest('.favorite-button');
                if (favoriteButton) {
                    e.preventDefault();
                    toggleFavorite(favoriteButton);
                }
            });
        });

        /**
         * Cambia el estado visual de un botón de favorito.
         * @param {HTMLElement} button El botón que fue presionado.
         */
        function toggleFavorite(button) {
            const icon = button.querySelector('i');
            const isActive = button.classList.contains('active');

            if (isActive) {
                button.classList.remove('active');
                icon.className = 'far fa-heart'; // Corazón vacío
            } else {
                button.classList.add('active');
                icon.className = 'fas fa-heart'; // Corazón lleno
            }
            // Aquí podrías añadir una llamada AJAX para guardar el estado en el servidor.
        }

        //================================================
        // MANEJO DE FORMULARIOS DE "AGREGAR AL CARRITO"
        //================================================
        document.addEventListener('submit', function(e) {
            // Verifica si el formulario enviado es para agregar al carrito
            if (e.target.classList.contains('add-to-cart-form')) {
                const button = e.target.querySelector('.add-to-cart-btn, .add-button'); // Compatible con ambos tipos de botón
                if (button) {
                    const originalText = button.innerHTML;

                    // Muestra un estado de carga para dar feedback al usuario
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AGREGANDO...';
                    button.disabled = true;

                    // Simula un tiempo de espera y restaura el botón
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                }
            }
        });
    </script>

</body>

</html>