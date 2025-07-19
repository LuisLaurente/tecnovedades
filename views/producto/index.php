<?php $base = '/TECNOVEDADES/public/'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="<?= $base ?>css/producto-index.css">
    <style>
        .producto-card {
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
        }
        .acciones {
            margin-top: 8px;
        }
        .categoria-lista {
            color: #555;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .filtros-container { margin-bottom: 20px; }
        .loading { display: none; color: orange; }
        
        /* Estilos para filtros activos */
        .filtro-tag {
            display: inline-block;
            background: #007cba;
            color: white;
            padding: 4px 8px;
            margin: 2px 4px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .filtro-tag.total {
            background: #28a745;
        }
        
        /* Botones */
        #btnFiltrar, #btnLimpiar {
            padding: 8px 15px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #btnFiltrar {
            background: #007cba;
            color: white;
        }
        #btnFiltrar:hover {
            background: #005a8b;
        }
        #btnLimpiar {
            background: #dc3545;
            color: white;
        }
        #btnLimpiar:hover {
            background: #b02a37;
        }
        #btnFiltrar:disabled, #btnLimpiar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Contenedores de estado */
        #errorFiltros {
            animation: fadeIn 0.3s ease-in;
        }
        #filtrosActivos {
            animation: fadeIn 0.3s ease-in;
        }
        #productosContainer {
            min-height: 100px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <h1>Listado de Productos</h1>

    <a href="<?= $base ?>cargaMasiva/descargarPlantilla">📥 Descargar Plantilla CSV</a><br>

    <form action="<?= $base ?>cargaMasiva/procesarCSV" method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo_csv" accept=".csv" required>
        <button type="submit">📤 Subir CSV</button>
    </form>

    <a href="<?= $base ?>producto/crear">+ Nuevo Producto</a><br><br>

    <!-- Filtros -->
    <div class="filtros-container">
        <h3>🔍 Filtros de búsqueda</h3>

        <?php if (isset($estadisticasPrecios)): ?>
            <div>
                <span>Rango disponible: S/ <?= $estadisticasPrecios['precio_minimo'] ?> - S/ <?= $estadisticasPrecios['precio_maximo'] ?></span><br>
                <span>Promedio: S/ <?= $estadisticasPrecios['precio_promedio'] ?></span><br>
                <span>Total productos: <?= $estadisticasPrecios['total_productos'] ?></span>
            </div>
        <?php endif; ?>

        <form id="filtroForm" method="GET" action="<?= $base ?>producto">
            <!-- Filtros por precio -->
            <label>Precio mínimo (S/):</label>
            <input type="number" id="min_price" name="min_price" value="<?= $_GET['min_price'] ?? '' ?>" step="1" min="0"><br>

            <label>Precio máximo (S/):</label>
            <input type="number" id="max_price" name="max_price" value="<?= $_GET['max_price'] ?? '' ?>" step="1" min="0"><br>

            <!-- Filtro por categoría -->
            <label>Categoría:</label>
            <select id="categoria" name="categoria">
                <option value="">-- Todas --</option>
                <?php foreach ($categoriasDisponibles as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= ($_GET['categoria'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nombre']) ?> (<?= $categoria['total_productos'] ?>)
                    </option>
                <?php endforeach; ?>
            </select><br>

            <!-- Filtro por etiquetas -->
            <fieldset>
                <legend>Etiquetas:</legend>
                <?php foreach ($todasEtiquetas as $etiqueta): ?>
                    <label>
                        <input type="checkbox" class="etiqueta-checkbox" name="etiquetas[]" value="<?= $etiqueta['id'] ?>"
                            <?= in_array($etiqueta['id'], $_GET['etiquetas'] ?? []) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($etiqueta['nombre']) ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>

            <!-- Disponibilidad -->
            <label>
                <input type="checkbox" id="disponibles" name="disponibles" value="1" <?= isset($_GET['disponibles']) ? 'checked' : '' ?>>
                Solo productos disponibles (stock > 0)
            </label><br>

            <!-- Orden -->
            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden">
                <option value="">-- Seleccionar --</option>
                <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a mayor</option>
                <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a menor</option>
                <option value="nombre_asc" <?= ($_GET['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre: A-Z</option>
                <option value="nombre_desc" <?= ($_GET['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre: Z-A</option>
                <option value="fecha_desc" <?= ($_GET['orden'] ?? '') === 'fecha_desc' ? 'selected' : '' ?>>Más recientes</option>
            </select><br><br>

            <button type="button" id="btnFiltrar">🔍 Filtrar</button>
            <button type="button" id="btnLimpiar">❌ Limpiar filtros</button>

            <span id="loading" class="loading">⏳ Cargando...</span>
        </form>
    </div>

    <!-- Contenedor para mostrar errores -->
    <div id="errorFiltros" style="display: none; color: red; margin: 10px 0; padding: 10px; border: 1px solid red; border-radius: 5px;">
        <strong>❌ Errores en filtros:</strong>
        <ul id="listaErrores"></ul>
    </div>

    <!-- Contenedor para mostrar filtros activos -->
    <div id="filtrosActivos" style="display: none; margin: 10px 0; padding: 10px; background: #e8f5e8; border-radius: 5px;">
        <strong>🔍 Filtros activos:</strong>
        <div id="infoFiltros"></div>
    </div>

    <!-- Productos -->
    <div id="productosContainer"><?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                <?= htmlspecialchars($producto['descripcion']) ?><br>
                Precio: S/ <?= number_format($producto['precio'], 2) ?><br>
                Visible: <?= $producto['visible'] ? 'Sí' : 'No' ?><br>

                <?php if (!empty($producto['categorias'])): ?>
                    <div class="categoria-lista">
                        Categorías: <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                    </div>
                <?php else: ?>
                    <div class="categoria-lista">Sin categoría</div>
                <?php endif; ?>

                <div class="acciones">
                    <a href="<?= $base ?>producto/editar/<?= $producto['id'] ?>">Editar</a> |
                    <a href="<?= $base ?>producto/eliminar/<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?></div>

    <script src="<?= $base ?>js/producto-filtros.js?v=<?= time() ?>"></script>
</body>
</html>
