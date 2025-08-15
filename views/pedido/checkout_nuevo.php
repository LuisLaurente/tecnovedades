<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . url('pedido/precheckout'));
    exit;
}

use Core\Helpers\PromocionHelper;
use Models\Cupon;

$errores = [];
if (isset($_SESSION['errores_checkout']) && is_array($_SESSION['errores_checkout'])) {
    $errores = $_SESSION['errores_checkout'];
}
unset($_SESSION['errores_checkout']);

$usuario = $_SESSION['usuario'];

// Obtener direcciones del usuario
$direcciones = [];
try {
    $conexion = \Core\Database::getConexion();
    $stmt = $conexion->prepare("SELECT * FROM direcciones WHERE usuario_id = ? AND activa = 1 ORDER BY es_principal DESC, created_at DESC");
    $stmt->execute([$usuario['id']]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si no existen las tablas migradas, usar direccion del usuario
    $direcciones = [];
}

// Obtener detalles del usuario
$usuario_detalles = [];
try {
    $conexion = \Core\Database::getConexion();
    $stmt = $conexion->prepare("SELECT * FROM usuario_detalles WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $usuario_detalles = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    // Si no existen las tablas migradas, usar datos del usuario
    $usuario_detalles = [
        'telefono' => $usuario['telefono'] ?? ''
    ];
}

// Preparar datos de carrito
$productosDetallados = [];
$carrito = $_SESSION['carrito'] ?? [];

if (!empty($carrito)) {
    $productoModel = new \Models\Producto();
    foreach ($carrito as $clave => $item) {
        $producto = $productoModel->obtenerPorId($item['producto_id']);
        if ($producto) {
            $producto['nombre'] = $producto['nombre'];
            $producto['cantidad'] = $item['cantidad'];
            $producto['talla'] = $item['talla'];
            $producto['color'] = $item['color'];
            $producto['clave'] = $clave;
            $producto['precio'] = $item['precio'];
            $producto['subtotal'] = $producto['precio'] * $item['cantidad'];
            $productosDetallados[] = $producto;
        }
    }
}

// Calcular promociones y totales
$promociones = PromocionHelper::evaluar($carrito, $usuario);
$totales = PromocionHelper::calcularTotales($carrito, $promociones);

// Aplicar cupón si existe
$cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
$descuento_cupon = 0;
if ($cupon_aplicado) {
    if ($cupon_aplicado['tipo'] === 'descuento_porcentaje') {
        $descuento_cupon = $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
    } elseif ($cupon_aplicado['tipo'] === 'descuento_fijo') {
        $descuento_cupon = min($cupon_aplicado['valor'], $totales['subtotal']);
    }
}

$total_final = max(0, $totales['total'] - $descuento_cupon);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TecnoVedades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .checkout-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .address-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .address-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .address-card.selected {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
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
            <p class="text-white/80">Revisa tu pedido y confirma los datos de envío</p>
        </div>

        <!-- Información del usuario -->
        <div class="checkout-card rounded-2xl shadow-2xl p-6 mb-8 max-w-2xl mx-auto">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">👋 Hola, <?= htmlspecialchars($usuario['nombre']) ?>!</h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($usuario['email']) ?></p>
                </div>
                <div class="flex-1 text-right">
                    <a href="<?= url('auth/logout') ?>" class="text-sm text-gray-500 hover:text-gray-700">
                        Cambiar cuenta
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-8">
            
            <!-- Columna izquierda: Formulario de checkout -->
            <div class="space-y-6">
                
                <!-- Errores -->
                <?php if (!empty($errores)): ?>
                    <div class="checkout-card rounded-2xl shadow-2xl p-6">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Selección de dirección -->
                <div class="checkout-card rounded-2xl shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📍 Dirección de Envío</h3>
                    
                    <?php if (!empty($direcciones)): ?>
                        <div class="space-y-3 mb-4">
                            <?php foreach ($direcciones as $index => $direccion): ?>
                                <div class="address-card p-4 border-2 border-gray-200 rounded-lg" 
                                     data-direccion='<?= json_encode($direccion) ?>'
                                     onclick="selectAddress(this)">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-800">
                                                    <?= htmlspecialchars($direccion['nombre_direccion'] ?: ucfirst($direccion['tipo'])) ?>
                                                </span>
                                                <?php if ($direccion['es_principal']): ?>
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Principal</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($direccion['direccion']) ?></p>
                                            <?php if ($direccion['distrito']): ?>
                                                <p class="text-xs text-gray-500">
                                                    <?= htmlspecialchars($direccion['distrito']) ?><?= $direccion['provincia'] ? ', ' . $direccion['provincia'] : '' ?><?= $direccion['departamento'] ? ', ' . $direccion['departamento'] : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="radio-button w-4 h-4 border-2 border-gray-300 rounded-full"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" id="toggleNewAddress" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            + Agregar nueva dirección
                        </button>
                    <?php endif; ?>
                    
                    <!-- Formulario para nueva dirección o datos básicos -->
                    <div id="newAddressForm" class="<?= !empty($direcciones) ? 'hidden' : '' ?> mt-4">
                        <form method="POST" action="<?= url('/pedido/registrar') ?>" id="checkoutForm">
                            <!-- Datos del usuario (siempre visible) -->
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                    <input type="text" name="nombre" required 
                                           value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                                    <input type="tel" name="telefono" required 
                                           value="<?= htmlspecialchars($usuario_detalles['telefono'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="999 999 999">
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección completa *</label>
                                    <textarea name="direccion" required rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="Av. Principal 123, Urbanización..."></textarea>
                                </div>
                                
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                                        <input type="text" name="distrito"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Ej: Lima">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                                        <input type="text" name="provincia"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Ej: Lima">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                                        <input type="text" name="departamento"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Ej: Lima">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Referencia (opcional)</label>
                                    <input type="text" name="referencia"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Ej: Casa amarilla frente al parque">
                                </div>

                                <!-- Opción para guardar dirección -->
                                <div class="flex items-center space-x-2 p-3 bg-blue-50 rounded-lg">
                                    <input type="checkbox" id="guardar_direccion" name="guardar_direccion" value="1" checked
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="guardar_direccion" class="text-sm text-gray-700">
                                        💾 <strong>Guardar esta dirección</strong> para futuras compras
                                    </label>
                                </div>
                                
                                <!-- Tipo de dirección si se va a guardar -->
                                <div id="tipoDereccion" class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de dirección</label>
                                        <select name="tipo_direccion" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="casa">🏠 Casa</option>
                                            <option value="trabajo">🏢 Trabajo</option>
                                            <option value="envio">📦 Solo envío</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre (opcional)</label>
                                        <input type="text" name="nombre_direccion"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="Ej: Casa de mamá">
                                    </div>
                                </div>
                            </div>

                            <!-- Campos ocultos para dirección seleccionada -->
                            <input type="hidden" id="direccion_id_seleccionada" name="direccion_id" value="">
                            
                            <!-- Botón de envío -->
                            <div class="mt-6">
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                    🚀 Confirmar Pedido - S/ <?= number_format($total_final, 2) ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Resumen del pedido -->
            <div class="space-y-6">
                <!-- Resumen de productos -->
                <div class="checkout-card rounded-2xl shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📋 Resumen del Pedido</h3>
                    
                    <?php if (!empty($productosDetallados)): ?>
                        <div class="space-y-4">
                            <?php foreach ($productosDetallados as $item): ?>
                                <div class="flex items-center space-x-4 pb-4 border-b border-gray-100 last:border-b-0">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-500 text-xs">IMG</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-800"><?= htmlspecialchars($item['nombre']) ?></h4>
                                        <div class="text-sm text-gray-600">
                                            <?php if ($item['talla']): ?>
                                                <span>Talla: <?= htmlspecialchars($item['talla']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['color']): ?>
                                                <span class="<?= $item['talla'] ? 'ml-2' : '' ?>">Color: <?= htmlspecialchars($item['color']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Cantidad: <?= $item['cantidad'] ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">S/ <?= number_format($item['subtotal'], 2) ?></div>
                                        <div class="text-sm text-gray-500">S/ <?= number_format($item['precio'], 2) ?> c/u</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Totales -->
                        <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span>S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                            </div>
                            
                            <?php if (($totales['descuento'] ?? 0) > 0): ?>
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Descuento promociones:</span>
                                    <span>-S/ <?= number_format($totales['descuento'], 2) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($cupon_aplicado && $descuento_cupon > 0): ?>
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Cupón "<?= htmlspecialchars($cupon_aplicado['codigo']) ?>":</span>
                                    <span>-S/ <?= number_format($descuento_cupon, 2) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Envío:</span>
                                <span class="text-green-600">Gratis</span>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between text-lg font-bold text-gray-800">
                                <span>Total:</span>
                                <span class="text-blue-600">S/ <?= number_format($total_final, 2) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay productos en el carrito</p>
                    <?php endif; ?>
                </div>

                <!-- Información adicional -->
                <div class="checkout-card rounded-2xl shadow-2xl p-6">
                    <h4 class="font-bold text-gray-800 mb-3">🔒 Compra Segura</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>• ✅ Tus datos están protegidos</p>
                        <p>• 📦 Envío gratuito a todo el país</p>
                        <p>• 🔄 Garantía de satisfacción</p>
                        <p>• 📱 Seguimiento en tiempo real</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedAddressCard = null;

        function selectAddress(card) {
            // Deseleccionar dirección anterior
            if (selectedAddressCard) {
                selectedAddressCard.classList.remove('selected');
                selectedAddressCard.querySelector('.radio-button').style.background = '';
            }

            // Seleccionar nueva dirección
            selectedAddressCard = card;
            card.classList.add('selected');
            card.querySelector('.radio-button').style.background = 'white';

            // Obtener datos de la dirección
            const direccionData = JSON.parse(card.dataset.direccion);
            
            // Llenar el formulario con los datos de la dirección seleccionada
            document.getElementById('direccion_id_seleccionada').value = direccionData.id;
            document.querySelector('textarea[name="direccion"]').value = direccionData.direccion;
            document.querySelector('input[name="distrito"]').value = direccionData.distrito || '';
            document.querySelector('input[name="provincia"]').value = direccionData.provincia || '';
            document.querySelector('input[name="departamento"]').value = direccionData.departamento || '';
            document.querySelector('input[name="referencia"]').value = direccionData.referencia || '';

            // Ocultar campos de nueva dirección y opciones de guardado
            document.getElementById('newAddressForm').querySelector('.space-y-4').style.display = 'none';
            document.getElementById('tipoDereccion').style.display = 'none';
            document.querySelector('input[name="guardar_direccion"]').checked = false;

            // Mostrar el formulario de envío
            document.getElementById('newAddressForm').classList.remove('hidden');
        }

        // Toggle para nueva dirección
        document.getElementById('toggleNewAddress')?.addEventListener('click', function() {
            const form = document.getElementById('newAddressForm');
            const isHidden = form.classList.contains('hidden');
            
            if (isHidden) {
                form.classList.remove('hidden');
                this.textContent = '- Usar dirección guardada';
                
                // Limpiar selección de direcciones
                if (selectedAddressCard) {
                    selectedAddressCard.classList.remove('selected');
                    selectedAddressCard.querySelector('.radio-button').style.background = '';
                    selectedAddressCard = null;
                }
                
                // Limpiar formulario y mostrar campos
                document.getElementById('direccion_id_seleccionada').value = '';
                form.querySelector('.space-y-4').style.display = 'block';
                document.getElementById('tipoDereccion').style.display = 'grid';
                document.querySelector('input[name="guardar_direccion"]').checked = true;
                
                // Limpiar campos del formulario
                document.querySelector('textarea[name="direccion"]').value = '';
                document.querySelector('input[name="distrito"]').value = '';
                document.querySelector('input[name="provincia"]').value = '';
                document.querySelector('input[name="departamento"]').value = '';
                document.querySelector('input[name="referencia"]').value = '';
            } else {
                form.classList.add('hidden');
                this.textContent = '+ Agregar nueva dirección';
            }
        });

        // Mostrar/ocultar campos de tipo de dirección según checkbox
        document.getElementById('guardar_direccion')?.addEventListener('change', function() {
            const tipoDiv = document.getElementById('tipoDereccion');
            if (this.checked) {
                tipoDiv.style.display = 'grid';
            } else {
                tipoDiv.style.display = 'none';
            }
        });

        // Preseleccionar primera dirección si existe
        document.addEventListener('DOMContentLoaded', function() {
            const firstAddress = document.querySelector('.address-card');
            if (firstAddress) {
                selectAddress(firstAddress);
            }
        });
    </script>
</body>
</html>
