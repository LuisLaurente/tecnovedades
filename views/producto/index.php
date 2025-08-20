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

<link rel="stylesheet" href="<?= url('css/min/producto-index.min.css') ?>">


<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <!-- Incluir header superior fijo -->
            <div class="sticky top-0 z-40">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </div>

            <div class="flex-1 p-6 bg-gray-50 overflow-y-auto productos-page">
                <div class="max-w-7xl mx-auto">
                    <!-- Header de la página -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">📦 Gestión de Productos</h1>
                        <p class="text-gray-600">Administra el catálogo completo de productos</p>
                    </div>

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
                        <a href="<?= url('carrito/ver') ?>" class="boton-carrito"> Ver Carrito<?php if ($cantidadEnCarrito > 0): ?>
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
                        <!-- Productos (Vista Administrador) -->
                        <div id="productosContainer">
                            <?php if (!empty($productos)): ?>
                                <table class="admin-productos-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio Final</th>
                                            <th>Precio Original</th>
                                            <th>% Descuento</th>
                                            <th>Visible</th>
                                            <th>Categorías</th>
                                            <th>Imágenes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos as $producto): ?>
                                            <tr>
                                                <td><?= $producto['id'] ?></td>
                                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                                <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                                                <td>S/ <?= number_format($producto['precio'], 2) ?></td>
                                                <td>
                                                    <?php if (!empty($producto['precio_tachado']) && $producto['precio_tachado'] > $producto['precio']): ?>
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
                                                <td class="<?= $producto['visible'] ? 'disponible' : 'no-disponible' ?>">
                                                    <?= $producto['visible'] ? 'Sí' : 'No' ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($producto['categorias'])): ?>
                                                        <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                                                    <?php else: ?>
                                                        <span class="sin-categoria">Sin categoría</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="imagenes-columna">
                                                    <?php if (!empty($producto['imagenes'])): ?>
                                                        <?php foreach ($producto['imagenes'] as $imagen): ?>
                                                            <img src="<?= url('uploads/' . $imagen['nombre_imagen']) ?>"
                                                                alt="Imagen de <?= htmlspecialchars($producto['nombre']) ?>"
                                                                class="imagen-miniatura">
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <span class="sin-imagen">Sin imágenes</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="acciones">
                                                    <a href="<?= url('producto/editar/' . $producto['id']) ?>">✏️ Editar</a>
                                                    <a href="<?= url('producto/eliminar/' . $producto['id']) ?>"
                                                        class="eliminar"
                                                        onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️ Eliminar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                                    <div class="text-gray-400 text-6xl mb-4">📦</div>
                                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay productos disponibles</h3>
                                    <p class="text-gray-500">Aún no se han agregado productos al catálogo</p>
                                </div>
                            <?php endif; ?>
                        </div>


                        <!-- CSS para tabla de administración -->
                        <style>
                            .admin-productos-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                                background: #fff;
                                border: 1px solid #ddd;
                            }

                            .admin-productos-table th,
                            .admin-productos-table td {
                                border: 1px solid #ddd;
                                padding: 8px 12px;
                                text-align: left;
                            }

                            .admin-productos-table th {
                                background: #f4f4f4;
                                font-weight: bold;
                            }

                            .disponible {
                                color: green;
                                font-weight: bold;
                            }

                            .no-disponible {
                                color: red;
                                font-weight: bold;
                            }

                            .imagenes-columna img.imagen-miniatura {
                                width: 50px;
                                height: 50px;
                                object-fit: cover;
                                margin-right: 5px;
                                border: 1px solid #ccc;
                                border-radius: 4px;
                            }

                            .acciones a {
                                display: inline-block;
                                margin-right: 8px;
                                color: #007bff;
                                text-decoration: none;
                            }

                            .acciones a.eliminar {
                                color: #d9534f;
                            }

                            .sin-categoria,
                            .sin-imagen {
                                color: #888;
                                font-style: italic;
                            }
                        </style>

                </div>

                <!-- Footer -->
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </div>
        </div>

        <script src="<?= url('js/min/producto-filtros.min.js') ?>?v=<?= time() ?>"></script>
</body>

</html>