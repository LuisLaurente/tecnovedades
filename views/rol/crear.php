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
        <!-- Encabezado -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Rol</h1>
                    <p class="text-gray-600 mt-2">Define un nuevo rol con permisos específicos para el sistema</p>
                </div>
                <a href="<?= url('/rol') ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        <!-- Mensajes de estado -->
        <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <?= htmlspecialchars(urldecode($_GET['error'])) ?>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow-sm p-8">
            <form method="POST" action="<?= url('/rol/store') ?>" class="space-y-6">
                <!-- Información básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Rol *
                            </label>
                            <input type="text" 
                                   id="nombre" 
                                   name="nombre" 
                                   required 
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                   placeholder="Ej: Moderador, Vendedor, etc."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estado
                            </label>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="activo" 
                                       name="activo" 
                                       <?= ($_POST['activo'] ?? true) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="activo" class="ml-2 text-sm text-gray-900">
                                    Rol activo
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mt-6">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea id="descripcion" 
                                  name="descripcion" 
                                  rows="3" 
                                  placeholder="Describe las responsabilidades y alcance de este rol..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Permisos -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Permisos del Sistema</h3>
                    <p class="text-sm text-gray-600 mb-6">Selecciona los permisos que tendrá este rol en el sistema</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($permisosDisponibles as $permiso => $descripcion): ?>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-start">
                                <input type="checkbox" 
                                       id="permiso_<?= $permiso ?>" 
                                       name="permisos[]" 
                                       value="<?= $permiso ?>"
                                       <?= in_array($permiso, $_POST['permisos'] ?? []) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1">
                                <div class="ml-3">
                                    <label for="permiso_<?= $permiso ?>" class="text-sm font-medium text-gray-900 cursor-pointer">
                                        <?= ucfirst(str_replace('_', ' ', $permiso)) ?>
                                    </label>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?= htmlspecialchars($descripcion) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="<?= url('/rol') ?>" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition duration-200">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Crear Rol
                    </button>
                </div>
            </form>
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
