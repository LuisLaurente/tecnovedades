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

            <div class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="max-w-4xl mx-auto p-4 bg-white shadow-md rounded-lg">
                    <h1 class="text-2xl font-bold mb-4">👤 Mi Perfil</h1>

                    <!-- Mensajes -->
                    <?php if (!empty($_GET['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_GET['error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Información del perfil -->
                        <div class="bg-white shadow rounded-lg border">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    📝 Información Personal
                                </h3>

                                <form method="POST" action="<?= url('/auth/updateProfile') ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Nombre -->
                                        <div>
                                            <label for="nombre" class="block text-sm font-medium text-gray-700">
                                                Nombre completo
                                            </label>
                                            <input type="text"
                                                name="nombre"
                                                id="nombre"
                                                value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700">
                                                Correo electrónico
                                            </label>
                                            <input type="email"
                                                name="email"
                                                id="email"
                                                value="<?= htmlspecialchars($usuario['email']) ?>"
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        </div>

                                        <!-- Información de solo lectura -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Rol asignado
                                            </label>
                                            <div class="mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md">
                                                <span class="text-sm text-gray-900"><?= htmlspecialchars($rol['descripcion']) ?></span>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?= htmlspecialchars($rol['nombre']) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Estado de cuenta -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Estado de cuenta
                                            </label>
                                            <div class="mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $usuario['activo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                    <?= $usuario['activo'] ? '✅ Activa' : '❌ Inactiva' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Permisos -->
                                    <div class="mt-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            🔑 Permisos asignados
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <?php
                                            // Asegurar que permisos es un array
                                            $permisos = $rol['permisos'] ?? [];
                                            if (is_string($permisos)) {
                                                $permisos = json_decode($permisos, true) ?: [];
                                            }

                                            if (!empty($permisos) && is_array($permisos)): ?>
                                                <?php foreach ($permisos as $permiso): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <?= htmlspecialchars($permiso) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">Sin permisos específicos</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="submit"
                                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            💾 Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Información de sesión -->
                        <div class="bg-white shadow rounded-lg border">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    🕒 Información de Sesión
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Sesión iniciada</dt>
                                        <dd class="mt-1 text-lg font-semibold text-blue-600">
                                            <?= date('H:i:s', $_SESSION['login_time'] ?? time()) ?>
                                        </dd>
                                        <dd class="text-xs text-gray-500">
                                            <?= date('d/m/Y', $_SESSION['login_time'] ?? time()) ?>
                                        </dd>
                                    </div>

                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Última actividad</dt>
                                        <dd class="mt-1 text-lg font-semibold text-green-600">
                                            <?= date('H:i:s', $_SESSION['last_activity'] ?? time()) ?>
                                        </dd>
                                        <dd class="text-xs text-gray-500">
                                            <?= date('d/m/Y', $_SESSION['last_activity'] ?? time()) ?>
                                        </dd>
                                    </div>

                                    <div class="bg-purple-50 p-4 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">ID de usuario</dt>
                                        <dd class="mt-1 text-lg font-semibold text-purple-600">
                                            #<?= $usuario['id'] ?>
                                        </dd>
                                        <dd class="text-xs text-gray-500">
                                            Identificador único
                                        </dd>
                                    </div>

                                    <div class="bg-yellow-50 p-4 rounded-lg">
                                        <dt class="text-sm font-medium text-gray-500">Tiempo de sesión</dt>
                                        <dd class="mt-1 text-lg font-semibold text-yellow-600">
                                            <?php
                                            $tiempoSesion = time() - ($_SESSION['login_time'] ?? time());
                                            $horas = floor($tiempoSesion / 3600);
                                            $minutos = floor(($tiempoSesion % 3600) / 60);
                                            echo sprintf('%02d:%02d', $horas, $minutos);
                                            ?>
                                        </dd>
                                        <dd class="text-xs text-gray-500">
                                            Horas:Minutos
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <div class="bg-white shadow rounded-lg border">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    🔒 Seguridad
                                </h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Contraseña protegida</span>
                                            <p class="text-xs text-gray-500">Para cambiar tu contraseña, contacta al administrador del sistema.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
            </div>
        </main>
         </div>
    </div>
</body>

</html>