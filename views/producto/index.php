<?php $base = '/TECNOVEDADES/public/'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
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
    </style>
</head>

<body>
    <h1>Listado de Productos</h1>

    <a href="/cargaMasiva/descargarPlantilla">📥 Descargar Plantilla CSV</a><br>

    <!-- 📤 Formulario para subir CSV -->
    <form id="formCSV" action="/TECNOVEDADES/public/cargaMasiva/procesarCSV" method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo_csv" accept=".csv" id="archivo_csv" required>
        <button type="submit">📤 Subir CSV</button>
    </form>

    <form method="GET" action="/TECNOVEDADES/public/producto">
        <fieldset>
            <legend>Filtrar por etiquetas:</legend>
            <?php foreach ($todasEtiquetas as $etiqueta): ?>
                <label>
                    <input type="checkbox" name="etiquetas[]" value="<?= $etiqueta['id'] ?>"
                        <?= in_array($etiqueta['id'], $_GET['etiquetas'] ?? []) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($etiqueta['nombre']) ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <fieldset>
            <label>
                <input type="checkbox" name="disponibles" value="1"
                    <?= isset($_GET['disponibles']) && $_GET['disponibles'] == '1' ? 'checked' : '' ?>>
                Mostrar solo productos disponibles (stock > 0)
            </label>
        </fieldset>

        <button type="submit">🔍 Filtrar</button>
        <a href="/TECNOVEDADES/public/producto">Limpiar</a>
    </form>

    <label>
        <input type="checkbox" name="disponibles" value="1" <?= isset($_GET['disponibles']) && $_GET['disponibles'] == '1' ? 'checked' : '' ?>>
        Solo productos disponibles (stock > 0)
    </label>

    <form method="GET" id="filtroOrden">
        <label for="orden">Ordenar por:</label>
        <select name="orden" id="orden" onchange="document.getElementById('filtroOrden').submit()">
            <option value="">-- Seleccionar --</option>
            <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a mayor</option>
            <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a menor</option>
            <option value="nombre_asc" <?= ($_GET['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre: A-Z</option>
            <option value="nombre_desc" <?= ($_GET['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre: Z-A</option>
            <option value="fecha_desc" <?= ($_GET['orden'] ?? '') === 'fecha_desc' ? 'selected' : '' ?>>Más recientes</option>
        </select>

        <?php
        // 👇 Mantener filtros anteriores si estaban activos
        if (!empty($_GET['etiquetas'])) {
            foreach ($_GET['etiquetas'] as $et) {
                echo '<input type="hidden" name="etiquetas[]" value="' . htmlspecialchars($et) . '">';
            }
        }
        if (!empty($_GET['disponibles'])) {
            echo '<input type="hidden" name="disponibles" value="1">';
        }
        ?>
    </form>



    <!-- Nuevo producto -->

    <br>

    <a href="<?= $base ?>producto/crear">+ Nuevo Producto</a>



    <?php if (!empty($productos)): ?>
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
                    <a href="<?= $base ?>producto/editar/<?= $producto['id'] ?>">Editar</a> |
                    <a href="<?= $base ?>producto/eliminar/<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?>
</body>

</html>