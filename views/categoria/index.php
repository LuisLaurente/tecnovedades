<?php
$categoriaNombre = isset($categoria['nombre']) ? htmlspecialchars($categoria['nombre']) : "Categoría";
$metaTitle = "Productos en la categoría {$categoriaNombre} | Tienda Tecnovedades";
$metaDescription = "Encuentra los mejores productos en la categoría {$categoriaNombre} a precios increíbles.";
?>

<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>

        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <!-- Incluir header superior fijo -->
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-6xl mx-auto">
                        <!-- Header de la página -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-800 mb-2">📂 Gestión de Categorías</h1>
                                    <p class="text-gray-600">Organiza y administra las categorías de productos</p>
                                </div>
                                <a href="<?= url('categoria/crear') ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nueva Categoría
                                </a>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="bg-white rounded-lg shadow-md p-6">

                            <?php if (!empty($categorias)): ?>

                                <?php
                                // Definir función una sola vez para evitar redeclaración accidental
                                if (!function_exists('mostrarCategorias')) {
                                    /**
                                     * Mostrar categorías en forma jerárquica.
                                     *
                                     * @param array $categorias Array plano de categorías (cada item debe contener id, id_padre, nombre, imagen, tiene_hijos, tiene_productos)
                                     * @param int|null $padre
                                     * @param int $nivel
                                     * @return void
                                     */
                                    function mostrarCategorias(array $categorias, $padre = null, $nivel = 0)
                                    {
                                        foreach ($categorias as $categoria) {
                                            // Comparación flexible: null/0/'' manejados
                                            $catPadre = $categoria['id_padre'] ?? null;
                                            if ((string)$catPadre !== '' && $padre === null) {
                                                // si estamos en raíz (padre=null) y la categoría tiene id_padre distinto de null/0, saltarla
                                                if ($catPadre != $padre) {
                                                    // continue checking other items
                                                }
                                            }
                                            // Normalizar comparación (acepta null/0/'')
                                            if ((string)$catPadre == (string)$padre) {

                                                $margin = $nivel * 20;
                                                $safeName = htmlspecialchars($categoria['nombre']);
                                                // Imagen o fallback
                                                $imgFile = !empty($categoria['imagen']) ? $categoria['imagen'] : null;
                                                $imgUrl = $imgFile ? url('uploads/categorias/' . $imgFile) : url('uploads/default-category.png');

                                                // Flags
                                                $tieneHijos = !empty($categoria['tiene_hijos']);
                                                $tieneProductos = !empty($categoria['tiene_productos']);

                                                // Permisos simples
                                                $puedeEliminar = !$tieneHijos && !$tieneProductos;
                                                $puedeEditar = true; // permitimos editar siempre; si quieres bloquear, usar la misma lógica que eliminar

                                                // Contenedor de la categoría
                                                ?>
                                                <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500 mb-3 flex items-center justify-between" style="margin-left: <?= (int)$margin ?>px;">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-16 h-16 flex-shrink-0 rounded-md overflow-hidden bg-white border">
                                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= $safeName ?>" style="width:100%; height:100%; object-fit:cover;">
                                                        </div>

                                                        <div>
                                                            <h3 class="font-semibold text-gray-800"><?= $safeName ?></h3>
                                                            <?php if ($tieneHijos): ?>
                                                                <div class="text-sm text-blue-600">📁 Tiene subcategorías</div>
                                                            <?php elseif ($tieneProductos): ?>
                                                                <div class="text-sm text-green-600">📦 Contiene productos</div>
                                                            <?php else: ?>
                                                                <div class="text-sm text-gray-500">—</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <?php if ($puedeEditar): ?>
                                                            <?php $urlEditar = url("categoria/editar/{$categoria['id']}"); ?>
                                                            <a href="<?= $urlEditar ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors flex items-center gap-2">
                                                                <span aria-hidden="true">✏️</span>
                                                                <span class="sr-only">Editar <?= $safeName ?></span>
                                                                <span class="text-sm">Editar</span>
                                                            </a>
                                                        <?php endif; ?>

                                                        <?php if ($puedeEliminar): ?>
                                                            <?php $urlEliminar = url("categoria/eliminar/{$categoria['id']}"); ?>
                                                            <a href="<?= $urlEliminar ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors" onclick="return confirm('¿Estás seguro de eliminar la categoría <?= addslashes($safeName) ?>?')">
                                                                <span aria-hidden="true">🗑️</span>
                                                                <span class="sr-only">Eliminar <?= $safeName ?></span>
                                                            </a>
                                                        <?php else: ?>
                                                            <!-- Mostrar botón deshabilitado para indicar que no se puede eliminar -->
                                                            <button type="button" class="bg-gray-200 text-gray-600 px-3 py-1 rounded text-sm" disabled title="No se puede eliminar mientras tenga subcategorías o productos">
                                                                🗑️ Eliminar
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php
                                                // Llamada recursiva para hijos
                                                mostrarCategorias($categorias, $categoria['id'], $nivel + 1);
                                            }
                                        }
                                    }
                                }
                                ?>

                                <div class="space-y-3">
                                    <?php mostrarCategorias($categorias); ?>
                                </div>

                            <?php else: ?>
                                <div class="text-center py-12">
                                    <div class="text-gray-400 text-6xl mb-4">📂</div>
                                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay categorías registradas</h3>
                                    <p class="text-gray-500 mb-4">Comienza creando tu primera categoría</p>
                                    <a href="<?= url('categoria/crear') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                        Crear Categoría
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
