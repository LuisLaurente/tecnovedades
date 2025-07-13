<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="/css/producto-index.css">
</head>

<body>
    <h1>Listado de Productos</h1>

    <a href="/cargaMasiva/descargarPlantilla">📥 Descargar Plantilla CSV</a><br>

    <!-- 📤 Formulario para subir CSV -->
    <form action="/cargaMasiva/procesarCSV" method="POST" enctype="multipart/form-data" style="display: inline-block;">
        <input type="file" name="archivo_csv" accept=".csv" required>
        <button type="submit">📤 Subir CSV</button>
    </form>
    <br>

    <a href="/producto/crear">+ Nuevo Producto</a><br><br>

    <!-- Filtros de productos -->
    <div class="filtros-container">
        <h3 class="filtros-titulo">🔍 Filtros de búsqueda</h3>
        
        <?php if (isset($estadisticasPrecios)): ?>
            <div class="estadisticas-precios">
                <span>Rango disponible: S/ <?= $estadisticasPrecios['precio_minimo'] ?> - S/ <?= $estadisticasPrecios['precio_maximo'] ?></span>
                <span>Promedio: S/ <?= $estadisticasPrecios['precio_promedio'] ?></span>
                <span>Total productos: <?= $estadisticasPrecios['total_productos'] ?></span>
            </div>
        <?php endif; ?>

        <form id="filtroForm" method="GET" action="/producto/index" class="filtros-form">
            <!-- Filtros de precio -->
            <div class="filtro-grupo">
                <h4>Filtros de precio</h4>
                <div class="filtro-precio">
                    <div>
                        <label class="filtro-label">Precio mínimo (S/):</label>
                        <input type="number" id="min_price" name="min_price" step="1" min="0" 
                               class="filtro-input"
                               value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>"
                               placeholder="<?= isset($estadisticasPrecios) ? $estadisticasPrecios['precio_minimo'] : '0' ?>">
                    </div>
                    <div>
                        <label class="filtro-label">Precio máximo (S/):</label>
                        <input type="number" id="max_price" name="max_price" step="1" min="0" 
                               class="filtro-input"
                               value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>"
                               placeholder="<?= isset($estadisticasPrecios) ? $estadisticasPrecios['precio_maximo'] : '999' ?>">
                    </div>
                </div>
            </div>

            <!-- Filtros de categoría -->
            <div class="filtro-grupo">
                <h4>🏷️ Filtro por categoría</h4>
                <div class="filtro-categoria">
                    <label class="filtro-label">Categoría:</label>
                    <select id="categoria" name="categoria" class="filtro-select">
                        <option value="">-- Todas las categorías --</option>
                        <?php if (isset($categoriasDisponibles) && !empty($categoriasDisponibles)): ?>
                            <?php foreach ($categoriasDisponibles as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" 
                                        <?= (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?> 
                                    (<?= $categoria['total_productos'] ?> productos)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div>
                <button type="button" id="btnFiltrar" class="btn-filtrar">
                    🔍 Filtrar
                </button>
                <button type="button" id="btnLimpiar" class="btn-limpiar">
                    ❌ Limpiar filtros
                </button>
                <span id="loading" class="loading">
                    ⏳ Cargando...
                </span>
            </div>
        </form>

        <!-- Mostrar errores de validación -->
        <div id="errorFiltros" class="error-filtros">
            <strong>⚠️ Errores en los filtros:</strong>
            <ul id="listaErrores"></ul>
        </div>

        <!-- Mostrar filtros activos -->
        <div id="filtrosActivos" class="filtros-activos">
            <strong>Filtros activos:</strong>
            <span id="infoFiltros"></span>
        </div>
    </div>

    <!-- Contenedor de productos -->
    <div id="productosContainer" class="productos-container"><?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                <?= htmlspecialchars($producto['descripcion']) ?><br>
                Precio: S/ <?= number_format($producto['precio'], 2) ?><br>
                Visible: <?= $producto['visible'] ? 'Sí' : 'No' ?><br>

                <!-- Mostrar categorías -->
                <?php if (!empty($producto['categorias'])): ?>
                    <div class="categoria-lista">
                        Categorías: <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                    </div>
                <?php else: ?>
                    <div class="categoria-lista">Sin categoría</div>
                <?php endif; ?>

                <div class="acciones">
                    <a href="/producto/editar/<?= $producto['id'] ?>">Editar</a> |
                    <a href="/producto/eliminar/<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?></div>

    <script src="/js/producto-filtros.js"></script>
</body>

</html>