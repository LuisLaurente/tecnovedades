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
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<link rel="stylesheet" href="<?= url('css/producto-index.css') ?>">

<body>
    <div class="page-container">
        <!-- Navegación lateral fija -->
        <div class="sidebar">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="main-content">
            <!-- Header superior fijo -->
            <div class="header">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </div>

            <div class="content">
                <div class="container">
                    <!-- Header de la página -->
                    <div class="page-header">
                        <h1 class="page-title">📦 Gestión de Productos</h1>
                        <p class="page-description">Administra el catálogo completo de productos</p>
                    </div>

                    <!-- Alerta carrito -->
                    <?php if (isset($_SESSION['mensaje_carrito'])): ?>
                        <div id="mensaje-alerta" class="alerta-carrito">
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

                    <!-- Acciones superiores -->
                    <div class="acciones-superiores">
                        <a href="<?= url('cargaMasiva/descargarPlantilla') ?>" class="boton-accion">📥 Descargar Plantilla CSV</a>
                        <a href="<?= url('cargaMasiva/gestionImagenes') ?>" class="boton-accion">📸 Gestión Imágenes</a>
                        <a href="<?= url('carrito/ver') ?>" class="boton-accion boton-carrito">Ver Carrito
                            <?php if ($cantidadEnCarrito > 0): ?>
                                <span class="carrito-contador"><?= $cantidadEnCarrito ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('producto/crear') ?>" class="boton-accion">+ Nuevo Producto</a>
                    </div>

                    <!-- Formulario de carga CSV -->
                    <form action="<?= url('cargaMasiva/procesarCSV') ?>" method="POST" enctype="multipart/form-data" class="form-csv">
                        <input type="file" name="archivo_csv" accept=".csv" required class="input-accion">
                        <button type="submit" class="boton-accion">📤 Subir CSV</button>
                    </form>

                    <!-- Filtros -->
                    <div class="filtros-container">
                        <h3>🔍 Filtros de búsqueda</h3>

                        <?php if (isset($estadisticasPrecios)): ?>
                            <div class="estadisticas-precios">
                                <span>Rango disponible: S/ <?= $estadisticasPrecios['precio_minimo'] ?> - S/ <?= $estadisticasPrecios['precio_maximo'] ?></span>
                                <span>Promedio: S/ <?= $estadisticasPrecios['precio_promedio'] ?></span>
                                <span>Total productos: <?= $estadisticasPrecios['total_productos'] ?></span>
                            </div>
                        <?php endif; ?>

                        <form id="filtroForm" method="GET" action="<?= url('producto') ?>">
                            <!-- Filtros por precio -->
                            <div class="filtro-grupo">
                                <label for="min_price">Precio mínimo (S/):</label>
                                <input type="number" id="min_price" name="min_price" value="<?= $_GET['min_price'] ?? '' ?>" step="1" min="0" class="input-accion">
                            </div>
                            <div class="filtro-grupo">
                                <label for="max_price">Precio máximo (S/):</label>
                                <input type="number" id="max_price" name="max_price" value="<?= $_GET['max_price'] ?? '' ?>" step="1" min="0" class="input-accion">
                            </div>

                            <!-- Filtro por categoría -->
                            <div class="filtro-grupo">
                                <label for="categoria">Categoría:</label>
                                <select id="categoria" name="categoria" class="input-accion">
                                    <option value="">-- Todas --</option>
                                    <?php foreach ($categoriasDisponibles as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>" <?= ($_GET['categoria'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoria['nombre']) ?> (<?= $categoria['total_productos'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filtro por etiquetas -->
                            <div class="filtro-grupo">
                                <fieldset>
                                    <legend>Etiquetas:</legend>
                                    <?php foreach ($todasEtiquetas as $etiqueta): ?>
                                        <label class="etiqueta-label">
                                            <input type="checkbox" class="etiqueta-checkbox" name="etiquetas[]" value="<?= $etiqueta['id'] ?>"
                                                <?= in_array($etiqueta['id'], $_GET['etiquetas'] ?? []) ? 'checked' : '' ?>>
                                            <?= htmlspecialchars($etiqueta['nombre']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                            </div>

                            <!-- Disponibilidad -->
                            <div class="filtro-grupo">
                                <label class="disponibles-label">
                                    <input type="checkbox" id="disponibles" name="disponibles" value="1" <?= isset($_GET['disponibles']) ? 'checked' : '' ?>>
                                    Solo productos disponibles (stock > 0)
                                </label>
                            </div>

                            <!-- Orden -->
                            <div class="filtro-grupo">
                                <label for="orden">Ordenar por:</label>
                                <select name="orden" id="orden" class="input-accion">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio: Menor a mayor</option>
                                    <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio: Mayor a menor</option>
                                    <option value="nombre_asc" <?= ($_GET['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre: A-Z</option>
                                    <option value="nombre_desc" <?= ($_GET['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre: Z-A</option>
                                    <option value="fecha_desc" <?= ($_GET['orden'] ?? '') === 'fecha_desc' ? 'selected' : '' ?>>Más recientes</option>
                                </select>
                            </div>

                            <div class="filtro-acciones">
                                <button type="button" id="btnFiltrar" class="boton-accion">🔍 Filtrar</button>
                                <button type="button" id="btnLimpiar" class="boton-accion boton-secundario">❌ Limpiar</button>
                            </div>

                            <span id="loading" class="loading">⏳ Cargando...</span>
                        </form>
                    </div>

                    <!-- Contenedor para mostrar errores -->
                    <div id="errorFiltros" class="error-filtros">
                        <strong>❌ Errores en filtros:</strong>
                        <ul id="listaErrores"></ul>
                    </div>

                    <!-- Contenedor para mostrar filtros activos -->
                    <div id="filtrosActivos" class="filtros-activos">
                        <strong>🔍 Filtros activos:</strong>
                        <div id="infoFiltros"></div>
                    </div>

                    <!-- Productos (Vista Administrador) -->
                    <div id="productosContainer">
                        <?php if (!empty($productos)): ?>
                            <div class="admin-productos-table-container">
                                <table class="admin-productos-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>SKU</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio</th>
                                            <th>Original</th>
                                            <th>Descuento</th>
                                            <th>Visible</th>
                                            <th>Categorías</th>
                                            <th>Imágenes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos as $producto): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($producto['id'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($producto['sku'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($producto['nombre'] ?? '') ?></td>
                                                <td class="descripcion-columna">
                                                    <?php
                                                    $desc = $producto['descripcion'] ?? '';
                                                    $shortDesc = strlen($desc) > 50 ? substr($desc, 0, 47) . '...' : $desc;
                                                    ?>
                                                    <span class="descripcion-corta"><?= htmlspecialchars($shortDesc) ?></span>
                                                    <?php if (strlen($desc) > 50): ?>
                                                        <span class="descripcion-tooltip"><?= htmlspecialchars($desc) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>S/ <?= number_format($producto['precio'] ?? 0, 2) ?></td>
                                                <td>
                                                    <?php if (!empty($producto['precio_tachado']) && $producto['precio_tachado'] > ($producto['precio'] ?? 0)): ?>
                                                        S/ <?= number_format($producto['precio_tachado'], 2) ?>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] > 0): ?>
                                                        <?= number_format($producto['porcentaje_descuento'], 2) ?>%
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td class="estado-<?= $producto['visible'] ? 'disponible' : 'no-disponible' ?>">
                                                    <?= $producto['visible'] ? 'Sí' : 'No' ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($producto['categorias'])): ?>
                                                        <span class="categorias-lista">
                                                            <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="sin-categoria">Sin categoría</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="imagenes-columna">
                                                    <?php if (!empty($producto['imagenes']) && is_array($producto['imagenes'])): ?>
                                                        <div class="imagenes-contenedor">
                                                            <img src="<?= url('Uploads/' . htmlspecialchars($producto['imagenes'][0]['nombre_imagen'] ?? '')) ?>"
                                                                 alt="Imagen de <?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?>"
                                                                 class="imagen-miniatura">
                                                            <?php if (count($producto['imagenes']) > 1): ?>
                                                                <span class="imagenes-contador">+<?= count($producto['imagenes']) - 1 ?></span>
                                                            <?php endif; ?>
                                                            <div class="imagenes-tooltip">
                                                                <?php foreach ($producto['imagenes'] as $imagen): ?>
                                                                    <img src="<?= url('Uploads/' . htmlspecialchars($imagen['nombre_imagen'] ?? '')) ?>"
                                                                         alt="Imagen adicional"
                                                                         class="imagen-tooltip">
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="sin-imagen">Sin imágenes</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="acciones">
                                                    <a href="<?= url('producto/editar/' . $producto['id']) ?>" class="accion-editar">✏️</a>
                                                    <a href="<?= url('producto/eliminar/' . $producto['id']) ?>" class="accion-eliminar"
                                                       onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">📦</div>
                                <h3 class="empty-title">No hay productos disponibles</h3>
                                <p class="empty-description">Aún no se han agregado productos al catálogo.</p>
                                <a href="<?= url('producto/crear') ?>" class="boton-accion">+ Agregar Producto</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script src="<?= url('js/min/producto-filtros.min.js') ?>?v=<?= time() ?>"></script>
</body>
</html>