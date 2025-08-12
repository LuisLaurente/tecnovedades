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
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nueva Categoría
                                </a>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="bg-white rounded-lg shadow-md p-6">

                            <?php if (!empty($categorias)): ?>
                                <div class="space-y-3">
                                    <?php
                                    function mostrarCategorias($categorias, $padre = null, $nivel = 0)
                                    {
                                        foreach ($categorias as $categoria) {
                                            if ($categoria['id_padre'] == $padre) {
                                                echo "<div class='bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500' style='margin-left: " . ($nivel * 20) . "px'>";
                                                echo "<div class='flex justify-between items-center'>";
                                                echo "<div>";
                                                echo "<h3 class='font-semibold text-gray-800'>" . htmlspecialchars($categoria['nombre']) . "</h3>";

                                                $puedeEditar = !$categoria['tiene_hijos'] && !$categoria['tiene_productos'];
                                                $puedeEliminar = !$categoria['tiene_hijos'] && !$categoria['tiene_productos'];

                                                if ($categoria['tiene_hijos']) {
                                                    echo "<span class='text-sm text-blue-600'>📁 Tiene subcategorías</span>";
                                                } elseif ($categoria['tiene_productos']) {
                                                    echo "<span class='text-sm text-green-600'>📦 Contiene productos</span>";
                                                }
                                                echo "</div>";

                                                echo "<div class='flex gap-2'>";
                                                if ($puedeEditar) {
                                                    $urlEditar = url("categoria/editar/{$categoria['id']}");
                                                    echo "<a href='$urlEditar' class='bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors'>✏️ Editar</a>";
                                                }

                                                if ($puedeEliminar) {
                                                    $urlEliminar = url("categoria/eliminar/{$categoria['id']}");
                                                    echo "<a href='$urlEliminar' class='bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors' onclick=\"return confirm('¿Estás seguro de eliminar esta categoría?')\">🗑️ Eliminar</a>";
                                                }
                                                echo "</div>";
                                                echo "</div>";
                                                echo "</div>";

                                                mostrarCategorias($categorias, $categoria['id'], $nivel + 1);
                                            }
                                        }
                                    }

                                    mostrarCategorias($categorias);
                                    ?>
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