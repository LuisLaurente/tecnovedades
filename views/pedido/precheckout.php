<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión para Continuar - TecnoVedades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .auth-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .benefit-item {
            transition: transform 0.2s ease;
        }
        .benefit-item:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="<?= url('carrito/ver') ?>" class="inline-flex items-center text-white hover:text-gray-200 mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al carrito
            </a>
            <h1 class="text-4xl font-bold text-white mb-2">🛒 Finalizar Compra</h1>
            <p class="text-white/80 text-lg">Para continuar necesitas iniciar sesión o crear una cuenta</p>
        </div>

        <!-- Resumen del carrito -->
        <div class="auth-card rounded-2xl shadow-2xl p-6 mb-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">📋 Resumen de tu compra</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                </div>
                <?php if (($totales['descuento'] ?? 0) > 0): ?>
                <div class="flex justify-between text-green-600">
                    <span>Descuento:</span>
                    <span>-S/ <?= number_format($totales['descuento'], 2) ?></span>
                </div>
                <?php endif; ?>
                <hr class="border-gray-200">
                <div class="flex justify-between text-lg font-bold text-gray-800">
                    <span>Total:</span>
                    <span class="text-blue-600">S/ <?= number_format($totales['total'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Opciones de autenticación -->
        <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-8">
            
            <!-- Ya tienes cuenta -->
            <div class="auth-card rounded-2xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">¿Ya tienes cuenta?</h2>
                    <p class="text-gray-600">Inicia sesión para acceder a tus direcciones guardadas</p>
                </div>
                
                <div class="space-y-4 mb-6">
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-600">✨</span>
                        <span class="text-sm text-gray-700">Direcciones guardadas</span>
                    </div>
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-600">📦</span>
                        <span class="text-sm text-gray-700">Historial de pedidos</span>
                    </div>
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-600">⚡</span>
                        <span class="text-sm text-gray-700">Proceso más rápido</span>
                    </div>
                </div>

                <a href="<?= url('auth/login?redirect=' . urlencode('pedido/checkout')) ?>" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 block text-center">
                    🔐 Iniciar Sesión
                </a>
            </div>

            <!-- Crear cuenta nueva -->
            <div class="auth-card rounded-2xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">¿Primera vez aquí?</h2>
                    <p class="text-gray-600">Crea tu cuenta y disfruta de todos los beneficios</p>
                </div>
                
                <div class="space-y-4 mb-6">
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600">💾</span>
                        <span class="text-sm text-gray-700">Guarda múltiples direcciones</span>
                    </div>
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600">🎯</span>
                        <span class="text-sm text-gray-700">Ofertas personalizadas</span>
                    </div>
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600">🔔</span>
                        <span class="text-sm text-gray-700">Notificaciones de estado</span>
                    </div>
                    <div class="benefit-item flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                        <span class="text-green-600">🏆</span>
                        <span class="text-sm text-gray-700">Programa de puntos</span>
                    </div>
                </div>

                <a href="<?= url('auth/registro?redirect=' . urlencode('pedido/checkout')) ?>" 
                   class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 block text-center">
                    ✨ Crear Cuenta Gratis
                </a>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="max-w-2xl mx-auto mt-12 text-center">
            <div class="auth-card rounded-2xl shadow-2xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🔒 ¿Por qué necesito crear una cuenta?</h3>
                <div class="text-sm text-gray-600 space-y-2">
                    <p>• <strong>Seguridad:</strong> Protegemos tus datos de pago y personales</p>
                    <p>• <strong>Conveniencia:</strong> Guardamos tus direcciones para futuras compras</p>
                    <p>• <strong>Seguimiento:</strong> Podrás ver el estado de tus pedidos en tiempo real</p>
                    <p>• <strong>Soporte:</strong> Te ayudamos mejor cuando conocemos tu historial</p>
                </div>
                <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-sm text-yellow-800">
                        💡 <strong>Tip:</strong> Tus datos se guardarán automáticamente para hacer tus próximas compras súper rápidas
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-white/60 py-8">
        <p>&copy; 2025 TecnoVedades. Compra segura y rápida.</p>
    </footer>

</body>
</html>
