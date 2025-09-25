<?php
// Asegura que la sesión esté activa
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
<body class="bg-gray-100 text-gray-900">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md fixed h-full">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </aside>

        <!-- Contenido principal -->
        <div class="flex-1 ml-64 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm p-4 sticky top-0 z-10">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </header>

            <!-- Contenido -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="bg-white rounded-2xl shadow p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">📂 Carga Masiva de Productos</h2>
                    <p class="text-gray-600 mb-6">Sube un archivo CSV para registrar o actualizar productos en el catálogo.</p>

                    <!-- Formulario -->
                    <form action="<?= url('cargaMasiva/procesarCSV') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                        <label for="archivo_csv" 
                            class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg cursor-pointer hover:bg-indigo-700 transition w-40 text-center">
                            📂 Seleccionar
                        </label>
                        <input 
                            id="archivo_csv" 
                            name="archivo_csv" 
                            type="file" 
                            accept=".csv" 
                            class="hidden"
                        />
                        <span id="nombreArchivo" class="ml-3 text-sm text-gray-600">Ningún archivo seleccionado</span>

                        <script>
                            const inputFile = document.getElementById("archivo_csv");
                            const nombreArchivo = document.getElementById("nombreArchivo");

                            inputFile.addEventListener("change", () => {
                                if (inputFile.files.length > 0) {
                                    nombreArchivo.textContent = inputFile.files[0].name;
                                } else {
                                    nombreArchivo.textContent = "Ningún archivo seleccionado";
                                }
                            });
                        </script>

                        </div>

                        <button 
                            type="submit" 
                            class="px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition"
                        >
                            📤 Procesar archivo
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= url('js/min/producto-filtros.min.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
