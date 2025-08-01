<?php
// Asegura que la sesión esté activa con verificador
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Inicializa contador del carrito
$cantidadEnCarrito = 0;

if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += $item['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="<?= url('css/producto-index.css') ?>">

    <!-- Alerta carrito -->
    <?php if (isset($_SESSION['mensaje_carrito'])): ?>
        <div id="mensaje-alerta" style="
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 9999;
            text-align: center;
            animation: fadein 0.5s;
        ">
            <?= $_SESSION['mensaje_carrito'] ?>
        </div>

        <script>
            setTimeout(() => {
                const alerta = document.getElementById('mensaje-alerta');
                if (alerta) {
                    alerta.style.display = 'none';
                }
            }, 3000);
        </script>
        <?php unset($_SESSION['mensaje_carrito']); ?>
    <?php endif; ?>


</head>

<body data-base-url="<?= url('') ?>">
    <!-- Lista -->
    <h1>Listado de Productos</h1>

    <a href="<?= url('cargaMasiva/descargarPlantilla') ?>">📥 Descargar Plantilla CSV</a><br>
    <!-- Carrito -->
    <a href="<?= url('carrito/ver') ?>" class="boton-carrito">🛒 Ver Carrito<?php if ($cantidadEnCarrito > 0): ?>
        <span style="
            position: absolute;
            top: -8px;
            right: -12px;
            background-color: rgb(245, 115, 8);
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 50%;
            font-weight: bold;
        ">
            <?= $cantidadEnCarrito ?>
        </span>
    <?php endif; ?>
    </a>
    <a href="<?= url('pedido/listar') ?>" class="boton-carrito" style="background:#007cba;float:right;right:180px;">📦 Listado de Pedidos (Vista Admin a prox implementación)</a>

    <form action="<?= url('cargaMasiva/procesarCSV') ?>" method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo_csv" accept=".csv" required>
        <button type="submit">📤 Subir CSV</button>
    </form>

    <a href="<?= url('producto/crear') ?>">+ Nuevo Producto</a><br><br>

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

        <form id="filtroForm" method="GET" action="<?= url('producto') ?>">
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
    <div id="productosContainer">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <div class="producto-card">
                    <div class="producto-info">
                        <div class="producto-titulo"><?= htmlspecialchars($producto['nombre']) ?></div>
                        <div class="producto-descripcion"><?= htmlspecialchars($producto['descripcion']) ?></div>
                        <div class="producto-precio">S/ <?= number_format($producto['precio'], 2) ?></div>
                        <div class="producto-visible <?= $producto['visible'] ? 'disponible' : 'no-disponible' ?>">
                            Visible: <?= $producto['visible'] ? 'Sí' : 'No' ?>
                        </div>

                        <?php if (!empty($producto['categorias'])): ?>
                            <div class="categoria-lista">
                                Categorías: <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="categoria-lista">Sin categoría</div>
                        <?php endif; ?>
                    </div>

                    <div class="acciones">
                        <a href="<?= url('producto/editar/' . $producto['id']) ?>">✏️ Editar</a>
                        <a href="<?= url('producto/eliminar/' . $producto['id']) ?>"
                            class="eliminar"
                            onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️ Eliminar</a>
                    </div>

                    <form method="POST" action="<?= url('carrito/agregar') ?>" class="carrito-form">
                        <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

                        <div class="carrito-form-row">
                            <div class="carrito-form-group">
                                <label>Talla:</label>
                                <input type="text" name="talla">
                            </div>
                            <div class="carrito-form-group">
                                <label>Color:</label>
                                <input type="text" name="color">
                            </div>
                        </div>

                        <div class="carrito-form-group">
                            <label>Cantidad:</label>
                            <input type="number" name="cantidad" value="1" min="1">
                        </div>

                        <button type="submit">🛒 Agregar al Carrito</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-productos">
                📦 No hay productos disponibles
            </div>
        <?php endif; ?>
    </div>

    <script src="<?= url('js/producto-filtros.js') ?>?v=<?= time() ?>"></script>
</body>

</html>