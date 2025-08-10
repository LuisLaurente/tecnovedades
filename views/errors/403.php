<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - TecnoVedades</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Acceso Denegado</h1>
        <p class="text-gray-600 mb-6">No tienes permisos para acceder a esta sección.</p>
        
        <div class="space-y-3">
            <a href="<?= url('/auth/profile') ?>" 
               class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                Ir a Mi Perfil
            </a>
            
            <a href="<?= url('/auth/logout') ?>" 
               class="block w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                Cerrar Sesión
            </a>
        </div>
        
        <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
        <div class="mt-6 p-4 bg-gray-50 rounded text-left text-sm">
            <h3 class="font-semibold mb-2">Debug Info:</h3>
            <p><strong>Usuario:</strong> <?= \Core\Helpers\SessionHelper::getUserName() ?></p>
            <p><strong>Rol:</strong> <?= \Core\Helpers\SessionHelper::getRole()['nombre'] ?? 'N/A' ?></p>
            <p><strong>Permisos:</strong> <?= implode(', ', \Core\Helpers\SessionHelper::getPermissions()) ?></p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
