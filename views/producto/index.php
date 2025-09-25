<?php
// Asegura que la sesión esté activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa contador del carrito
$cantidadEnCarrito = 0;
if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += (int)($item['cantidad'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<body class="bg-gray-50 text-gray-900" data-base-url="<?= htmlspecialchars(url('producto')) ?>">

<div class="flex h-screen">
  <!-- Sidebar -->
  <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r shadow-sm">
    <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
  </aside>

  <!-- Main -->
  <div class="flex-1 ml-64 flex flex-col min-h-screen">
    <!-- Header -->
    <div class="sticky top-0 z-40">
        <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
    </div>

    <!-- Content -->
    <main class="flex-1 overflow-y-auto p-6">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- Page header -->
        <div class="bg-white rounded-lg shadow p-6">
          <h1 class="text-3xl font-semibold text-gray-800 flex items-center gap-3">📦 Gestión de Productos</h1>
          <p class="text-gray-600 mt-1">Administra el catálogo completo de productos</p>
        </div>

        <!-- Session alert -->
        <?php if (isset($_SESSION['mensaje_carrito'])): ?>
          <div id="mensaje-alerta"
               class="fixed left-1/2 top-5 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-4">
            <span class="font-medium"><?= htmlspecialchars($_SESSION['mensaje_carrito']) ?></span>
            <button id="cerrarAlerta" class="text-white/90 hover:text-white">✖</button>
          </div>
          <?php unset($_SESSION['mensaje_carrito']); ?>
        <?php endif; ?>

        <!-- Acciones --> 
         <div class="flex flex-wrap gap-3 mb-6"> 
            <a href="<?= url('cargaMasiva/descargarPlantilla') ?>" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 shadow"> 📥 Descargar Plantilla CSV </a> 
            <a href="<?= url('cargaMasiva/gestionImagenes') ?>" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white hover:bg-purple-700 shadow"> 📸 Gestión Imágenes </a>
            <a href="<?= url('producto/crear') ?>" class="ml-2 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">➕ Nuevo Producto</a>
        </div>

        <!-- CSV upload card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-800 mb-3">📂 Carga masiva (CSV)</h2>
          <p class="text-sm text-gray-600 mb-4">Sube un CSV para crear o actualizar productos. Formato: (sku,nombre,descripcion,precio,...)</p>

          <form action="<?= url('cargaMasiva/procesarCSV') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <label for="archivo_csv" class="inline-block w-40 text-center px-4 py-2 bg-indigo-600 text-white rounded-lg cursor-pointer hover:bg-indigo-700 transition">
              📂 Elegir archivo
            </label>
            <input id="archivo_csv" name="archivo_csv" type="file" accept=".csv" required class="sr-only">
            <span id="archivoNombre" class="text-sm text-gray-600">Ningún archivo seleccionado</span>

            <button type="submit" class="ml-auto sm:ml-0 px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
              📤 Subir CSV
            </button>
          </form>
        </div>

        <!-- Filters card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-3">🔍 Filtros de búsqueda</h3>

          <?php if (!empty($estadisticasPrecios)): ?>
            <div class="text-sm text-gray-700 mb-4 space-y-1">
              <div>Rango: <span class="font-medium">S/ <?= htmlspecialchars($estadisticasPrecios['precio_minimo']) ?> - S/ <?= htmlspecialchars($estadisticasPrecios['precio_maximo']) ?></span></div>
              <div>Promedio: <span class="font-medium">S/ <?= htmlspecialchars($estadisticasPrecios['precio_promedio']) ?></span></div>
              <div>Total: <span class="font-medium"><?= htmlspecialchars($estadisticasPrecios['total_productos']) ?></span></div>
            </div>
          <?php endif; ?>

          <form id="filtroForm" method="GET" action="<?= url('producto') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Min price -->
            <div>
              <label for="min_price" class="block text-sm font-medium text-gray-700">Precio mínimo (S/):</label>
              <input id="min_price" name="min_price" type="number" step="1" min="0" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"
                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Max price -->
            <div>
              <label for="max_price" class="block text-sm font-medium text-gray-700">Precio máximo (S/):</label>
              <input id="max_price" name="max_price" type="number" step="1" min="0" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"
                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Category -->
            <div class="lg:col-span-1">
              <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría:</label>
              <select id="categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Todas --</option>
                <?php foreach ($categoriasDisponibles as $categoria): ?>
                  <option value="<?= htmlspecialchars($categoria['id']) ?>" <?= (($_GET['categoria'] ?? '') == $categoria['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categoria['nombre']) ?> (<?= htmlspecialchars($categoria['total_productos']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Tags full-width -->
            <fieldset class="sm:col-span-2 lg:col-span-3">
              <legend class="text-sm font-medium text-gray-700">Etiquetas:</legend>
              <div class="mt-2 flex flex-wrap gap-3">
                <?php foreach ($todasEtiquetas as $etiqueta): ?>
                  <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="etiquetas[]" value="<?= htmlspecialchars($etiqueta['id']) ?>"
                           <?= in_array($etiqueta['id'], $_GET['etiquetas'] ?? []) ? 'checked' : '' ?>
                           class="rounded text-indigo-600 focus:ring-indigo-500">
                    <?= htmlspecialchars($etiqueta['nombre']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>

            <!-- Disponibilidad -->
            <div class="sm:col-span-2">
              <label class="inline-flex items-center text-sm text-gray-700">
                <input type="checkbox" id="disponibles" name="disponibles" value="1" <?= isset($_GET['disponibles']) ? 'checked' : '' ?> class="mr-2 rounded text-indigo-600">
                Solo productos disponibles (stock &gt; 0)
              </label>
            </div>

            <!-- Orden -->
            <div>
              <label for="orden" class="block text-sm font-medium text-gray-700">Ordenar por:</label>
              <select id="orden" name="orden" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Seleccionar --</option>
                <option value="precio_asc" <?= (($_GET['orden'] ?? '') === 'precio_asc') ? 'selected' : '' ?>>Precio: Menor a mayor</option>
                <option value="precio_desc" <?= (($_GET['orden'] ?? '') === 'precio_desc') ? 'selected' : '' ?>>Precio: Mayor a menor</option>
                <option value="nombre_asc" <?= (($_GET['orden'] ?? '') === 'nombre_asc') ? 'selected' : '' ?>>Nombre: A-Z</option>
                <option value="nombre_desc" <?= (($_GET['orden'] ?? '') === 'nombre_desc') ? 'selected' : '' ?>>Nombre: Z-A</option>
                <option value="fecha_desc" <?= (($_GET['orden'] ?? '') === 'fecha_desc') ? 'selected' : '' ?>>Más recientes</option>
              </select>
            </div>

            <!-- Buttons -->
            <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-3">
              <button type="button" id="btnFiltrar" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">🔍 Filtrar</button>
              <button type="button" id="btnLimpiar" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">❌ Limpiar</button>
              <span id="loading" class="hidden text-sm text-gray-500">⏳ Cargando...</span>
            </div>
          </form>
        </div>

        <!-- Error & active filters placeholders -->
        <div id="errorFiltros" class="hidden bg-red-50 border border-red-200 text-red-700 rounded p-3">
          <strong>❌ Errores en filtros:</strong>
          <ul id="listaErrores" class="list-disc pl-6 mt-2 text-sm"></ul>
        </div>

        <div id="filtrosActivos" class="hidden bg-yellow-50 border border-yellow-200 text-yellow-800 rounded p-3">
          <strong>🔍 Filtros activos:</strong>
          <div id="infoFiltros" class="mt-1 text-sm"></div>
        </div>

        <!-- 🔍 Barra de búsqueda -->
        <div class="flex items-center max-w-md mx-auto mt-4 mb-6">
          <div class="relative w-full">
            <input 
              type="text" 
              id="busquedaProducto" 
              placeholder="Escriba para buscar..." 
              class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <!-- Icono lupa -->
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">🔍</span>
          </div>
        </div>

        <!-- 📦 Tabla de productos -->
        <div id="productosContainer" class="overflow-x-auto">
          <table id="tablaProductos" class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-3 py-2 text-left">ID</th>
                <th class="px-3 py-2 text-left">SKU</th>
                <th class="px-3 py-2 text-left">Nombre</th>
                <th class="px-3 py-2 text-left">Descripción</th>
                <th class="px-3 py-2 text-left">Precio</th>
                <th class="px-3 py-2 text-left">Original</th>
                <th class="px-3 py-2 text-left">% Desc.</th>
                <th class="px-3 py-2 text-left">Visible</th>
                <th class="px-3 py-2 text-left">Categorías</th>
                <th class="px-3 py-2 text-left">Imágenes</th>
                <th class="px-3 py-2 text-left">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($productos as $producto): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 align-top"><?= htmlspecialchars($producto['id'] ?? '') ?></td>
                <td class="px-3 py-2 align-top"><?= htmlspecialchars($producto['sku'] ?? '') ?></td>
                <td class="px-3 py-2 align-top"><?= htmlspecialchars($producto['nombre'] ?? '') ?></td>
                <td class="px-3 py-2 align-top">
                  <?php
                    $desc = $producto['descripcion'] ?? '';
                    $shortDesc = strlen($desc) > 60 ? substr($desc, 0, 57) . '...' : $desc;
                  ?>
                  <span title="<?= htmlspecialchars($desc) ?>"><?= htmlspecialchars($shortDesc) ?></span>
                </td>
                <td class="px-3 py-2 align-top">S/ <?= number_format($producto['precio'] ?? 0, 2) ?></td>
                <td class="px-3 py-2 align-top">
                  <?php if (!empty($producto['precio_tachado']) && $producto['precio_tachado'] > ($producto['precio'] ?? 0)): ?>
                    S/ <?= number_format($producto['precio_tachado'], 2) ?>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="px-3 py-2 align-top">
                  <?php if (!empty($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] > 0): ?>
                    <?= number_format($producto['porcentaje_descuento'], 2) ?>%
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="px-3 py-2 align-top font-semibold <?= !empty($producto['visible']) ? 'text-green-600' : 'text-red-600' ?>">
                  <?= !empty($producto['visible']) ? 'Sí' : 'No' ?>
                </td>
                <td class="px-3 py-2 align-top">
                  <?php if (!empty($producto['categorias'])): ?>
                    <?= implode(', ', array_map('htmlspecialchars', $producto['categorias'])) ?>
                  <?php else: ?>
                    <span class="text-gray-400 italic">Sin categoría</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 align-top">
                  <?php if (!empty($producto['imagenes']) && is_array($producto['imagenes'])): ?>
                    <div class="flex flex-wrap gap-2">
                      <?php foreach ($producto['imagenes'] as $imagen): ?>
                        <img src="<?= htmlspecialchars(url('uploads/' . ($imagen['nombre_imagen'] ?? ''))) ?>" alt="Img <?= htmlspecialchars($producto['nombre'] ?? '') ?>" class="w-12 h-12 object-cover rounded border">
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400 italic">Sin imágenes</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 align-top space-x-2">
                  <a href="<?= url('producto/editar/' . ($producto['id'] ?? '')) ?>" class="text-blue-600 hover:underline">✏️ Editar</a>
                  <a href="<?= url('producto/eliminar/' . ($producto['id'] ?? '')) ?>" class="text-red-600 hover:underline"
                     onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️ Eliminar</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- 🧩 Script búsqueda -->
        <script>
        document.getElementById('busquedaProducto').addEventListener('keyup', function() {
          let valor = this.value.toLowerCase();
          let filas = document.querySelectorAll("#tablaProductos tbody tr");

          filas.forEach(fila => {
            let celdas = fila.querySelectorAll("td");
            let coincide = false;

            celdas.forEach(celda => {
              if (celda.textContent.toLowerCase().includes(valor)) {
                coincide = true;
              }
            });

            fila.style.display = coincide ? "" : "none";
          });
        });
        </script>

      </div>

      <!-- Footer -->
      <div class="mt-6 max-w-7xl mx-auto">
        <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
      </div>
    </main>
  </div>
</div>

<!-- Scripts -->
<script>
  // Alert close & auto-hide
  (function(){
    const alerta = document.getElementById('mensaje-alerta');
    const btnCerrar = document.getElementById('cerrarAlerta');
    if (btnCerrar) btnCerrar.addEventListener('click', () => { if (alerta) alerta.style.display = 'none'; });
    if (alerta) setTimeout(()=> { alerta.style.display = 'none'; }, 3000);
  })();

  // File input custom label
  (function(){
    const input = document.getElementById('archivo_csv');
    const nombre = document.getElementById('archivoNombre');
    if (input) {
      input.addEventListener('change', () => {
        nombre.textContent = (input.files && input.files.length > 0) ? input.files[0].name : 'Ningún archivo seleccionado';
      });
    }
  })();

  // Filtrar / Limpiar con scroll a productos
  (function(){
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const form = document.getElementById('filtroForm');
    const loading = document.getElementById('loading');
    const baseUrl = document.body.dataset.baseUrl || '<?= htmlspecialchars(url('producto')) ?>';

    if (btnFiltrar && form) {
      btnFiltrar.addEventListener('click', (e) => {
        e.preventDefault();
        if (loading) loading.classList.remove('hidden');
        const url = form.getAttribute('action') + '?' + new URLSearchParams(new FormData(form)).toString();
        window.location.href = url + '#productosContainer';
      });
    }

    if (btnLimpiar) {
      btnLimpiar.addEventListener('click', () => {
        window.location.href = baseUrl + '#productosContainer';
      });
    }
  })();
</script>

</body>
</html>