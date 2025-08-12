<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - TecnoVedades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    TecnoVedades
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Crea tu cuenta gratuita
                </p>
                <?php if (!empty($redirect)): ?>
                    <p class="mt-1 text-xs text-green-600">
                        ✨ Después podrás finalizar tu compra
                    </p>
                <?php endif; ?>
            </div>

            <!-- Mensajes -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <!-- Beneficios -->
            <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <h4 class="text-sm font-semibold text-green-800 mb-2">🎉 Beneficios de tu cuenta:</h4>
                <ul class="text-xs text-green-700 space-y-1">
                    <li>• 💾 Guarda múltiples direcciones de envío</li>
                    <li>• 📦 Rastrea tus pedidos en tiempo real</li>
                    <li>• 🎯 Recibe ofertas personalizadas</li>
                    <li>• ⚡ Compras más rápidas en el futuro</li>
                </ul>
            </div>

            <!-- Formulario de registro -->
            <form class="mt-8 space-y-6" method="POST" action="<?= url('/auth/procesarRegistro') ?>" id="registroForm">
                <!-- Token CSRF para seguridad -->
                <?= \Core\Helpers\CsrfHelper::tokenField('registro_form') ?>
                
                <!-- Campo oculto para redirección -->
                <?php if (isset($redirect) && !empty($redirect)): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php endif; ?>
                
                <div class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">
                            Nombre Completo *
                        </label>
                        <input id="nombre" 
                               name="nombre" 
                               type="text" 
                               autocomplete="name" 
                               required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="Tu nombre completo"
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo Electrónico *
                        </label>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="tu@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña *
                        </label>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="new-password" 
                               required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="Mínimo 6 caracteres">
                        
                        <!-- Indicador de fuerza de contraseña -->
                        <div class="mt-2">
                            <div class="password-strength bg-gray-200" id="passwordStrength"></div>
                            <p class="text-xs text-gray-500 mt-1" id="passwordHint">
                                Mínimo 6 caracteres
                            </p>
                        </div>
                    </div>

                    <!-- Confirmar Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            Confirmar Contraseña *
                        </label>
                        <input id="confirm_password" 
                               name="confirm_password" 
                               type="password" 
                               autocomplete="new-password" 
                               required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="Repite tu contraseña">
                        <p class="text-xs text-gray-500 mt-1" id="passwordMatch"></p>
                    </div>
                </div>

                <!-- Términos y condiciones -->
                <div class="flex items-center">
                    <input id="terms" 
                           name="terms" 
                           type="checkbox" 
                           required
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-xs text-gray-700">
                        Acepto los <a href="#" class="text-green-600 hover:text-green-500 font-medium">términos y condiciones</a> 
                        y la <a href="#" class="text-green-600 hover:text-green-500 font-medium">política de privacidad</a>
                    </label>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" 
                            id="submitBtn"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-green-500 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </span>
                        <span id="submitText">Crear Cuenta Gratis</span>
                        <span id="submitSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </button>
                </div>

                <!-- Login link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes cuenta? 
                        <a href="<?= url('auth/login' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : '')) ?>" 
                           class="font-medium text-green-600 hover:text-green-500">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Volver al carrito -->
        <?php if (!empty($redirect) && $redirect === 'pedido/checkout'): ?>
            <div class="text-center">
                <a href="<?= url('carrito/ver') ?>" class="text-white hover:text-gray-200 text-sm">
                    ← Volver al carrito
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript para validaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registroForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordHint = document.getElementById('passwordHint');
            const passwordMatch = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            // Validación de fuerza de contraseña
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let hint = '';

                if (password.length >= 6) strength += 25;
                if (/[a-z]/.test(password)) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 25;

                if (strength < 25) {
                    passwordStrength.className = 'password-strength bg-red-400';
                    hint = 'Muy débil - Mínimo 6 caracteres';
                } else if (strength < 50) {
                    passwordStrength.className = 'password-strength bg-yellow-400';
                    hint = 'Débil - Agrega mayúsculas o números';
                } else if (strength < 75) {
                    passwordStrength.className = 'password-strength bg-blue-400';
                    hint = 'Buena - ¡Casi perfecta!';
                } else {
                    passwordStrength.className = 'password-strength bg-green-400';
                    hint = 'Excelente - Contraseña segura';
                }

                passwordStrength.style.width = strength + '%';
                passwordHint.textContent = hint;
                passwordHint.className = `text-xs mt-1 ${strength >= 50 ? 'text-green-600' : 'text-gray-500'}`;
            });

            // Validación de coincidencia de contraseñas
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;

                if (confirmPassword.length === 0) {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'text-xs text-gray-500 mt-1';
                } else if (password === confirmPassword) {
                    passwordMatch.textContent = '✓ Las contraseñas coinciden';
                    passwordMatch.className = 'text-xs text-green-600 mt-1';
                } else {
                    passwordMatch.textContent = '✗ Las contraseñas no coinciden';
                    passwordMatch.className = 'text-xs text-red-600 mt-1';
                }
            });

            // Envío del formulario
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return;
                }

                // Mostrar spinner
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitSpinner.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
